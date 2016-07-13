<?php
    /**
     * NWFireDispatchDisplayAT
     *
     * Displays the currently dispatches fire fighters in
     * 		- Upper Austria
     * 		- Lower Austria
     *
     * v 0.1
     *
     * by Kurt Höblinger aka NitricWare
     */
    class NWFireDispatchDisplayAT {
        /** @var string [URL to dispatches in Upper Austria] */
        public $urlUpperAustria = 'http://intranet.ooelfv.at/webext2/html/html_laufend.html';
        /** @var string [URL to dispatches in Lower Austia] */
        public $urlLowerAustria = 'http://www.feuerwehr-krems.at/codepages/wastl/wastlmain/Land_EinsatzAktuell.asp';
        /**
         * [loadPage Loads the HTML code.]
         * @param  [string] $url [The URL to the page listing the dispatches]
         * @return [string]      [Returns the plain HTML.]
         */
        private function loadPage($url){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            $output = curl_exec($curl);
            curl_close($curl);

            return $output;
        }

        /**
         * [loadDOM Parses the HTML code.]
         * @param  [string] $html [HTML code of the page listing the dispatches.]
         * @return [object]       [Returns a SimpleXML Object.]
         */
        private function loadDOM($html){
            $doc = new DOMDocument();
            $doc->loadHTML($html);
            $xml = simplexml_import_dom($doc);

            return $xml;
        }

        /**
         * [parseUpperAustria Parses the SimpleXML Object containg the dispatches from Upper Austia.]
         * @return [int] [Returns 0 when finished.]
         */
        public function parseUpperAustria(){
            $this->dispatches['upperAustria'] = [];
            $dispatch = 0;
            $dispatchPart = 1;

            $html = $this->loadPage($this->urlUpperAustria);
            $dom = $this->loadDOM($html);

            foreach ($dom->body->table->tr as $value) {
                switch ($value->attributes()->class) {
                    case 'etit':
                        $this->dispatches['upperAustria'][$dispatch]['location'] = (string) $value->td[0];
                        break;
                    case 'eart':
                        $this->dispatches['upperAustria'][$dispatch]['type'] = (string) $value->td[1];
                        break;
                    case 'edat':
                        $this->dispatches['upperAustria'][$dispatch]['date'] = substr((string) $value->td[1], 0, -2);
                        break;
                    case 'edet':
                        $this->dispatches['upperAustria'][$dispatch]['ID'] = explode('NUM1=',$value->td[2]->a->attributes()->href)[1];
                        break;
                    default:
                        # code...
                        break;
                }

                $dispatchPart++;
                if ($dispatchPart == 6){
                    echo $dispatchPart;
                    $dispatch++;
                    $dispatchPart = 1;
                }
            }
            return 0;
        }

        /**
         * [parseLowerAustria Parses the SimpleXML Object containg the dispatches from Upper Austia.]
         * @return [int] [Returns 0 when finished.]
         */
        public function parseLowerAustria(){
            $this->dispatches['lowerAustria'] = [];
            $dispatch = 0;

            $html = $this->loadPage($this->urlLowerAustria);
            $dom = $this->loadDOM($html);

            foreach ($dom->body->table->tr as $value){
                $this->dispatches['lowerAustria'][$dispatch]['location'] = (string) $value->td[1].' '.(string) $value->td[2];
                $this->dispatches['lowerAustria'][$dispatch]['type'] = (string) $value->td[3];
                $this->dispatches['lowerAustria'][$dispatch]['date'] = (string) $value->td[4];
                $this->dispatches['lowerAustria'][$dispatch]['ID'] = "UNKNOWN";

                $dispatch++;
            }

            return 0;
        }
    }
