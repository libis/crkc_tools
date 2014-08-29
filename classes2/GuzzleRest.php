<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 11/06/14
 * Time: 11:59
 */
require_once __CA_LIB_DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

class GuzzleRest {

    private $userid = null;
    private $paswoord = null;
    private $host = null;
    private $base = null;
    private $header = null;
    private $uri = null;

    # -------------------------------------------------------
    /**
     * Constructor
     *
     * @param string $ps_source MySQL URL
     * @param array $pa_options
     * @return bool
     */
    public function __construct($ini) {

        if (!$tools = parse_ini_file("../data/".$ini)) {
            die("Couldn't parse ini-file\n");
        }

        $this->userid = $tools['userid'];
        $this->paswoord = $tools['paswoord'];
        $this->host = $tools['host'];
        $this->base = $tools['base'];

        $this->uri = 'http://'.$this->userid.':'.$this->paswoord.'@'.$this->host ;

        $this->header = array('Content-Type' => 'application/json');

    }
    # -------------------------------------------------------
    /**
     * Destrutor
     *
     * @param string $ps_source MySQL URL
     * @param array $pa_options
     * @return bool
     */

    public function __destruct() {

    }
    # -------------------------------------------------------
    /**
     *
     *
     * @param string $ps_source MySQL URL
     * @param array $pa_options
     * @return bool
     */

    function createObject($update, $table) {

        $client = new Client($this->uri);

        $json_update = json_encode($update);

        $request = $client->put("/".$this->base."/service.php/item/".$table, $this->header, $json_update);
        $response = $request->send();
        $data = $response->json();
        echo $data;
        //if (isset($data['ok']) && ($data['ok'] == 1) && is_array($data['results'])) {
        return $data;
        //}
    }

    function postObject($update, $id, $table) {

        $client = new Client($this->uri);

        $json_update = json_encode($update);

        $curl_opt = array(
            "config"    =>
                array(
                    "curl"  =>
                        array(
                            CURLOPT_HTTPHEADER  =>
                                array('Content-Type' => 'application/json'),
                            CURLOPT_POST    =>  1,
                            CURLOPT_POSTFIELDS  =>  $json_update
                        )
                )
        );

        $request = $client->post("/".$this->base."/service.php/item/".$table."/id/".$id ."?pretty=1",$update, null);
        $response = $request->send();
        $data = $response->json();
        echo $data;
        //if (isset($data['ok']) && ($data['ok'] == 1) && is_array($data['results'])) {
        return $data;
        //}
    }

    function updateObject($update, $object_id, $table) {

        $client = new Client($this->uri);

        $json_update = json_encode($update);

        $request = $client->put("/".$this->base."/service.php/item/".$table."/id/".$object_id, $this->header, $json_update);
        $response = $request->send();
        $data = $response->json();

        return $data;
    }

    function findObject($query, $table) {

        $client = new Client($this->uri);

        $request = $client->get('/'.$this->base.'/service.php/find/'.$table.'?q='.$query.'&pretty=1&start=0&rows=100');
        $response = $request->send();
        $data = $response->json();

        return $data;

    }

    function getObject($id, $table) {

        $client = new Client($this->uri);

        $request = $client->get('/'.$this->base.'/service.php/item/'.$table.'/id/'.$id.'?pretty=1&format=edit');
        $response = $request->send();
        $data = $response->json();

        return $data;

    }

    function getObjectWithBundle($id, $table, $header) {

        $client = new Client($this->uri);

        $request = $client->get('/'.$this->base.'/service.php/item/'.$table.'/id/'.$id.'?pretty=1&format=edit, $header ');
        $response = $request->send();
        $data = $response->json();

        return $data;

    }

    function getFullObject($id, $table) {

        $client = new Client($this->uri);

        $request = $client->get('/'.$this->base.'/service.php/item/'.$table.'/id/'.$id.'?pretty=1&format=edit');
        $response = $request->send();
        $data = $response->json();

        return $data;

    }

} 