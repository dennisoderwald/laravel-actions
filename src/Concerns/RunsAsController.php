<?php

namespace Lorisleiva\Actions\Concerns;

use Illuminate\Http\Request;

trait RunsAsController
{
    protected $request;

    public function runAsController(Request $request)
    {
        $this->runningAs = 'controller';
        $this->request = $request;

        $this->reset($request->user());
        $this->fill($this->getAttributesFromRequest($request));

        $result = $this->run();

        if (method_exists($this, 'response')) {
            return $this->response($result, $request);
        }

        if (method_exists($this, 'jsonResponse') && $request->wantsJson()) {
            return $this->jsonResponse($result, $request);
        }

        if (method_exists($this, 'htmlResponse') && ! $request->wantsJson()) {
            return $this->htmlResponse($result, $request);
        }

        return $result;
    }

    public function getAttributesFromRequest(Request $request)
    {
        return array_merge(
            $this->getAttributesFromRoute($request),
            $request->all()
        );
    }

    public function getAttributesFromRoute(Request $request)
    {
        $route = $request->route();

        return $route ? $route->parametersWithoutNulls() : [];
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function middleware()
    {
        return [];
    }
    
    public function getMiddleware()
    {
        return array_map(function ($m) {
            return [
                'middleware' => $m,
                'options' => [],
            ];
        }, $this->middleware());
    }
    
    public function callAction($method, $parameters)
    {
        return $method === '__invoke'
            ? $this->runAsController(app(Request::class))
            : call_user_func_array([$this, $method], $parameters);
    }
}
