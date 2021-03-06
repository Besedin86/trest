<?php

/**
 * Class for HTTP requests via curl
 *
 * @author Marcos Mercedes <marcos.mercedesn@gmail.com>
 * @package TRest\Http
 */
namespace TRest\Http;

class TRestClient {

    const POST = 'POST';

    const GET = 'GET';

    const PUT = 'PUT';

    const DELETE = 'DELETE';

    /**
     * 
     * HTTP POST
     * 
     * @param TRestRequest $request
     * @return mixed
     */
    public function post(TRestRequest $request) {
        return $this->execute($request->setMethod(self::POST));
    }

    /**
     * 
     * HTTP GET
     * 
     * @param TRestRequest $request
     * @return mixed
     */
    public function get(TRestRequest $request) {
        return $this->execute($request->setMethod(self::GET));
    }

    /**
     * 
     * HTTP PUT
     * 
     * @param TRestRequest $request
     * @return mixed
     */
    public function put(TRestRequest $request) {
        return $this->execute($request->setMethod(self::PUT));
    }

    /**
     * 
     * HTTP DELETE
     * 
     * @param TRestRequest $request
     * @return mixed
     */
    public function delete(TRestRequest $request) {
        return $this->execute($request->setMethod(self::DELETE));
    }

    /**
     * 
     * creates a curl resource with the parameters provided by the {@link TRestRequest} object
     * 
     * @param TRestRequest $request
     * @return curl resource
     */
    private function getCurlInstance(TRestRequest $request) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->buildUrl());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Accept-Charset: utf-8'
        ));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        if ($request->getUsername() && $request->getPassword()) {
            curl_setopt($ch, CURLOPT_USERPWD, $request->getUsername() . ':' . $request->getPassword());
        }
        return $ch;
    }

    /**
     * 
     * sets the POST/PUT fields to the curl resource
     * 
     * @param curl resource $ch
     * @param TRestRequest $request
     * @return curl resource
     */
    private function setPostFields($ch, TRestRequest $request) {
        $entityProperties = $request->getEntity();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $entityProperties);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($entityProperties)
        ));
        return $ch;
    }

    /**
     * 
     * sets curl method to the curl resource handled by the request
     * 
     * @param curl resource $ch
     * @param TRestRequest $request
     * @return curl resource
     */
    private function setCurlMethod($ch, TRestRequest $request) {
        switch ($request->getMethod()) {
            case self::POST :
            case self::PUT :
                $ch = $this->setPostFields($ch, $request);
                break;
            case self::DELETE :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
                break;
            case self::GET :
            default :
                break;
        }
        return $ch;
    }

    /**
     * 
     * Executes http request 
     * 
     * @param TRestRequest $request
     * @throws \Exception if the response http code is 400 
     * @return mixed web service response
     */
    public function execute(TRestRequest $request) {
        $ch = $this->setCurlMethod($this->getCurlInstance($request), $request);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status >= 400) {
            curl_close($ch);
            throw new \Exception($result, $status);
        }
        curl_close($ch);
        return json_decode($result);
    }
}
