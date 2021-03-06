<?php

namespace Spark\Handler;

use Arbiter\ActionHandler as Arbiter;
use Arbiter\Action;
use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;
use Spark\Resolver;

class ActionHandler extends Arbiter
{
    /**
     * @var Resolver
     */
    protected $resolver;

    protected $actionAttribute = 'spark/adr:action';

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        callable               $next
    ) {
        /**
         * @var Action
         */
        $action     = $request->getAttribute($this->actionAttribute);
        $request = $request->withoutAttribute($this->actionAttribute);

        if (!($action instanceof Action)) {
            throw new \Exception(sprintf('"%s" request attribute does not implement Action', $this->actionAttribute));
        }

        // Resolve using the injector
        $resolver = $this->resolver;
        $domain    = $resolver($action->getDomain());
        $input     = $resolver($action->getInput());
        $responder = $resolver($action->getResponder());

        $payload  = $this->getPayload($domain, $input, $request);
        $response = $this->getResponse($responder, $request, $response, $payload);

        return $next($request, $response);
    }

    /**
     * Execute the domain to get a payload.
     *
     * @param  DomainInterface        $domain
     * @param  InputInterface         $input
     * @param  ServerRequestInterface $request
     * @return PayloadInterface
     */
    private function getPayload(
        DomainInterface        $domain,
        InputInterface         $input,
        ServerRequestInterface $request
    ) {
        return $domain($input($request));
    }

    /**
     * Execute the responder to marshall the reponse.
     *
     * @param  ResponderInterface     $responder
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  PayloadInterface       $payload
     * @return ResponseInterface
     */
    private function getResponse(
        ResponderInterface     $responder,
        ServerRequestInterface $request,
        ResponseInterface      $response,
        PayloadInterface       $payload
    ) {
        return $responder($request, $response, $payload);
    }
}
