<?php
class Payment_Adapter_BitcartCC implements \Box\InjectionAwareInterface
{
    private $config = array();

    protected $di;

    public function setDi($di)
    {
        $this->di = $di;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function __construct($config)
    {
        $this->config = $config;

        foreach (['api_endpoint', 'admin_url', 'store_id'] as $key) {
            if (!isset($this->config[$key])) {
                throw new \Box_Exception('Payment gateway BitcartCC is not configured. Please set ' . key);
            }
        }
    }

    public static function getConfig()
    {
        return array(
            'supports_one_time_payments' => true,
            'description' => 'Please refer to https://docs.bitcartcc.com/integrations/boxbilling for more details',
            'logo' => array(
                'logo' => '/BitcartCC/BitcartCC.png',
                'height' => '50px',
                'width' => '50px',
            ),
            'form' => array(
                'api_endpoint' => array('text', array(
                    'label' => 'Merchants API URL',
                ),
                ),
                'admin_url' => array('text', array(
                    'label' => 'Admin panel URL',
                ),
                ),
                'store_id' => array('text', array(
                    'label' => 'Store ID',
                ),
                ),
            ),
        );
    }

    public function getHtml($api_admin, $invoice_id, $subscription)
    {
        $invoice = $this->di['db']->load('Invoice', $invoice_id);
        $invoiceService = $this->di['mod_service']('Invoice');
        $payGatewayService = $this->di['mod_service']('Invoice', 'PayGateway');
        $payGateway = $this->di['db']->findOne('PayGateway', 'gateway = "BitcartCC"');
        $order_id = 'boxbilling-' . $invoice->id;
        $params = array(
            'price' => $invoiceService->getTotalWithTax($invoice),
            'store_id' => $this->config['store_id'],
            'currency' => $invoice->currency,
            'buyer_email' => $invoice->buyer_email,
            'order_id' => $order_id,
            'notification_url' => $this->config['notify_url'],
            'redirect_url' => $this->config['thankyou_url'],
        );
        $invoice = $this->send_request(sprintf('%s/%s', $this->config['api_endpoint'], 'invoices/order_id/' . urlencode($order_id)), $params);
        return $this->_generateForm($invoice->id);
    }

    public function send_request($url, $data, $post = 1)
    {
        $post_fields = json_encode($data);

        $request_headers = array();
        $request_headers[] = 'Content-Type: application/json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        curl_close($ch);

        return json_decode($result);
    }

    public function processTransaction($api_admin, $id, $data, $gateway_id)
    {
        $post_data = json_decode($data['http_raw_post_data'], true);
        $url_check = sprintf('%s/%s', $this->config['api_endpoint'], 'invoices/' . $post_data['id']);
        $bitcart_invoice = $this->send_request($url_check, array(), 0);
        if ($bitcart_invoice->status != 'complete') {
            throw new Payment_Exception("Invalid IPN sent");
        }
        if ($this->isIpnDuplicate($bitcart_invoice->id, $bitcart_invoice->price)) {
            throw new Payment_Exception('IPN is duplicate');
        }

        $invoice = $this->di['db']->getExistingModelById('Invoice', $data['get']['bb_invoice_id']);
        $tx = $this->di['db']->getExistingModelById('Transaction', $id);
        $tx->invoice_id = $invoice->id;
        $tx->txn_status = $bitcart_invoice->status;
        $tx->txn_id = $bitcart_invoice->id;
        $tx->amount = $bitcart_invoice->price;
        $tx->currency = $bitcart_invoice->currency;

        $bd = array(
            'amount' => $tx->amount,
            'description' => 'BitcartCC transaction ' . $bitcart_invoice->id,
            'type' => 'transaction',
            'rel_id' => $tx->id,
        );
        $client = $this->di['db']->getExistingModelById('Client', $invoice->client_id);
        $clientService = $this->di['mod_service']('client');
        $clientService->addFunds($client, $bd['amount'], $bd['description'], $bd);

        $invoiceService = $this->di['mod_service']('Invoice');
        if ($tx->invoice_id) {
            $invoiceService->payInvoiceWithCredits($invoice);
        }
        $invoiceService->doBatchPayWithCredits(array('client_id' => $client->id));

        $tx->status = 'processed';
        $tx->updated_at = date('Y-m-d H:i:s');
        $this->di['db']->store($tx);
    }

    protected function _generateForm($invoiceID)
    {
        $htmlOutput = '<button name = "bitcart-payment" class = "btn btn-success btn-sm" onclick = "showModal();return false;">Pay now</button>';
        $htmlOutput .= '<script src="' . $this->config['admin_url'] . '/modal/bitcart.js" type="text/javascript"></script>';
        $htmlOutput .= '<script type=\'text/javascript\'>';
        $htmlOutput .= 'function showModal() {';
        $htmlOutput .= 'bitcart.showInvoice(\'' . $invoiceID . '\');';
        $htmlOutput .= '}
                        </script>
                        </form>';
        return $htmlOutput;
    }

    public function isIpnDuplicate($txID, $txAmount)
    {
        $sql = 'SELECT id
                FROM transaction
                WHERE txn_id = :transaction_id
                  AND amount = :transaction_amount
                LIMIT 2';

        $bindings = array(
            ':transaction_id' => $txID,
            ':transaction_amount' => $txAmount,
        );

        $rows = $this->di['db']->getAll($sql, $bindings);
        if (count($rows) >= 1) {
            return true;
        }

        return false;
    }
}
