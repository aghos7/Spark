<?php
namespace Spark\Responder;

class JsonResponder extends AbstractResponder
{
    
    public static function accepts()
    {
        return ['application/json'];
    }

    protected function responseBody($data)
    {
        if (isset($data)) {
            $this->response = $this->response->withHeader('Content-Type', 'application/json');
            $this->response->getBody()->write(json_encode($data));
        }
    }
    
}
