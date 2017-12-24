<?php

	namespace Dblencowe\Wealth;

	use Dblencowe\Wealth\Exceptions\CurrencyException;

	class Poloniex {
        protected $api_key;
        protected $api_secret;
        protected $trading_url = "https://poloniex.com/tradingApi";
        protected $public_url = "https://poloniex.com/public";

        public function __construct($api_key, $api_secret) {
            $this->api_key = $api_key;
            $this->api_secret = $api_secret;
        }

        private function query(array $req = array()) {
            // API settings
            $key = $this->api_key;
            $secret = $this->api_secret;

            // generate a nonce to avoid problems with 32bit systems
            $mt = explode(' ', microtime());
            $req['nonce'] = $mt[1].substr($mt[0], 2, 6);

            // generate the POST data string
            $post_data = http_build_query($req, '', '&');
            $sign = hash_hmac('sha512', $post_data, $secret);

            // generate the extra headers
            $headers = array(
                    'Key: '.$key,
                    'Sign: '.$sign,
            );

            // curl handle (initialize if required)
            static $ch = null;
            if (is_null($ch)) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERAGENT,
                            'Mozilla/4.0 (compatible; Poloniex PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
                    );
            }
            curl_setopt($ch, CURLOPT_URL, $this->trading_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            // run the query
            $res = curl_exec($ch);

            if ($res === false) throw new Exception('Curl error: '.curl_error($ch));
            //echo $res;
            $dec = json_decode($res, true);


            if (!$dec){
                    //throw new Exception('Invalid data: '.$res);
                    return false;
            }else{
                    return $dec;
            }
        }

        protected function retrieveJSON($URL) {
            $opts = array('http' =>
                array(
                    'method'  => 'GET',
                    'timeout' => 10
                )
            );
            $context = stream_context_create($opts);
            $feed = file_get_contents($URL, false, $context);
            $json = json_decode($feed, true);
            return $json;
        }

        public function getBalances() {
            $balances = $this->query(
                array(
                    'command' => 'returnCompleteBalances'
                )
            );

            $nonZeroBalances = array();

            foreach ($balances as $currency => $attributes) {
							$balance = $attributes['available'] + $attributes['onOrders'];

            	if ( $balance > 0 ) {
								if ( $currency == "STR" ) { $currency = "XLM"; }
            		$nonZeroBalances[(string)$currency] = $balance;
            	}
						}

						return $nonZeroBalances;
        }

    }
 ?>
