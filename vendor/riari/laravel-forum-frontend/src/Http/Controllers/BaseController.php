<?php namespace Riari\Forum\Frontend\Http\Controllers;

use Forum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Riari\Forum\API\Dispatcher;
use Riari\Forum\Contracts\API\ReceiverContract;

abstract class BaseController extends Controller implements ReceiverContract
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a frontend controller instance.
     */
    public function __construct()
    {
        $this->dispatcher = new Dispatcher($this);
    }

    /**
     * Return a prepared API dispatcher instance.
     *
     * @param  string  $route
     * @param  array  $parameters
     * @return Dispatcher
     */
    protected function api($route, $parameters = [])
    {
        return $this->dispatcher->route("forum.api.{$route}", $parameters);
    }

    /**
     * Handle a response from the dispatcher for the given request.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return Response|mixed
     */
    public function handleResponse(Request $request, Response $response)
    {
        if ($response->getStatusCode() == 422) {
            $errors = $response->getOriginalContent()['validation_errors'];

            throw new HttpResponseException(
                redirect()->back()->withInput($request->input())->withErrors($errors)
            );
        }

        if ($response->getStatusCode() == 403) {
            abort(403);
        }

        return $response->isNotFound() ? abort(404) : $response->getOriginalContent();
    }

    /**
     * Helper: Bulk action response.
     *
     * @param  Collection  $models
     * @param  string  $transKey
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function bulkActionResponse(Collection $models, $transKey)
    {
        if ($models->count()) {
            Forum::alert('success', $transKey, $models->count());
        } else {
            Forum::alert('warning', 'general.invalid_selection');
        }

        return redirect()->back();
    }
}
