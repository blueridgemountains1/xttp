<?php

namespace JohnathanSmith\Xttp;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class XttpPending implements MakesXttpPending
{
    use Macroable;

    /** @var array */
    protected const METHODS = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];

    /** @var string */
    protected $method = 'GET';

    /** @var string */
    protected $url = '/';

    /** @var array */
    protected $options = ['headers' => [], 'cookies' => []];

    /** @var string */
    protected $bodyFormat = 'json';

    /** @var \JohnathanSmith\Xttp\HandlesXttpCookies */
    protected $cookies;

    /** @var Closure[] */
    protected $middleware = [];

    /** @var \GuzzleHttp\HandlerStack */
    protected $stack;

    public function __construct(
        HandlesXttpCookies $cookies
    ) {
        $this->cookies = $cookies;
        $this->stack = HandlerStack::create();
        $this->asJson();
    }

    public function asJson(): self
    {
        return $this->setBodyFormat('json')
            ->contentType('application/json');
    }

    public function contentType(string $contentType): self
    {
        $this->options['headers']['Content-Type'] = $contentType;

        return $this;
    }

    /**
     * @param  \JohnathanSmith\Xttp\HandlesXttpCookies|null  $cookies
     *
     * @return static
     */
    public static function new(
        HandlesXttpCookies $cookies = null
    ): self {
        return new static(
            $cookies ?? new XttpCookies(new CookieJar())
        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param  string  $method
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMethod(string $method): self
    {
        $method = strtoupper($method);
        if (! in_array($method, self::METHODS, true)) {
            throw new InvalidArgumentException("{$method} is not a valid HTTP Method");
        }
        $this->method = $method;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(iterable $options): self
    {
        $this->options = (array) $options;

        return $this;
    }

    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    public function addOption(iterable $option): self
    {
        $this->options = array_merge(
            $this->options,
            (array) $option
        );

        return $this;
    }

    public function addHeader(iterable $header): self
    {
        $this->options['headers'] = array_merge(
            $this->options['headers'] ?? [],
            (array) $header
        );

        return $this;
    }

    /**
     * @param  \JohnathanSmith\Xttp\ProcessesXttpRequests|null|\JohnathanSmith\Xttp\XttpProcessor  $processesXttpRequests
     * @param  \GuzzleHttp\ClientInterface|null|Client  $client
     *
     * @return \JohnathanSmith\Xttp\XttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null
    ): XttpResponse {
        return ($processesXttpRequests ?? new XttpProcessor())->process($this, $client ?? $this->getClient());
    }

    /**
     * @param  iterable|Closure  $middlewares
     * @param  bool  $prepend
     *
     * @return \JohnathanSmith\Xttp\XttpPending
     */
    public function withRequestMiddleware($middlewares, $prepend = false): self
    {
        return $this->loopMiddleware($middlewares, 'request', $prepend);
    }

    /**
     * @return Closure[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param  iterable|Closure  $middlewares
     * @param  bool  $prepend
     *
     * @return \JohnathanSmith\Xttp\XttpPending
     */
    public function withResponseMiddleware($middlewares, $prepend = false): self
    {
        return $this->loopMiddleware($middlewares, 'response', $prepend);
    }

    /**
     * @param Closure|Closure[] $middlewares
     * @param  bool  $prepend
     *
     * @return $this
     */
    public function withMiddleware($middlewares, $prepend = false)
    {
        $this->loopMiddleware($middlewares, null, $prepend);

        return $this;
    }

    public function withRetryMiddleware(Closure $decider, Closure $delay): self
    {
        return $this->withMiddleware(Middleware::retry($decider, $delay));
    }

    protected function loopMiddleware($middlewares, string $type = null, $prepend = false): self
    {
        if (is_iterable($middlewares)) {
            foreach ($middlewares as $middleware) {
                $this->singleMiddleware($middleware, $type, $prepend);
            }
        } else {
            $this->singleMiddleware($middlewares, $type, $prepend);
        }

        return $this;
    }

    public function singleMiddleware(Closure $middleware, string $type = null, $prepend = false): void
    {
        if ($type === 'request') {
            $m = Middleware::mapRequest($middleware);
        } elseif ($type === 'response') {
            $m = Middleware::mapResponse($middleware);
        } else {
            $m = $middleware;
        }

        if ($prepend) {
            array_unshift($this->middleware, $m);
        } else {
            $this->middleware[] = $m;
        }
    }

    public function clearMiddleware(): self
    {
        $this->middleware = [];

        return $this;
    }

    public function withoutRedirecting(): self
    {
        $this->options['allow_redirects'] = false;

        return $this;
    }

    public function withoutVerifying(): self
    {
        $this->options['verify'] = false;

        return $this;
    }

    public function asMultipart(): self
    {
        return $this->setBodyFormat('multipart');
    }

    public function getContentType(): string
    {
        return $this->getHeaders()['Content-Type'] ?? '';
    }

    public function getHeaders(): array
    {
        return $this->options['headers'] ?? [];
    }

    public function setHeaders(iterable $headers): self
    {
        $this->options['headers'] = (array) $headers;

        return $this;
    }

    public function asFormParams(): self
    {
        return $this->setBodyFormat('form_params')
            ->contentType('application/x-www-form-urlencoded');
    }

    public function accept(string $header): self
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    public function withHeaders(iterable $headers): self
    {
        $this->options = array_merge_recursive($this->options, [
            'headers' => (array) $headers,
        ]);

        return $this;
    }

    public function withBasicAuth($username, $password): self
    {
        $this->options['auth'] = [$username, $password];

        return $this;
    }

    public function withDigestAuth($username, $password): self
    {
        $this->options['auth'] = [$username, $password, 'digest'];

        return $this;
    }

    public function withCookies(iterable $cookies, string $domain = '/'): self
    {
        $this->cookies->add($cookies, $this->getDomain() ?? $domain);

        $this->options['cookies'] = $this->cookies->getJar();

        return $this;
    }

    protected function getDomain(): ?string
    {
        if (empty($this->url)) {
            return null;
        }

        return parse_url($this->url, PHP_URL_HOST);
    }

    public function timeout(int $seconds): self
    {
        $this->options['timeout'] = $seconds;

        return $this;
    }

    public function getBodyFormat(): string
    {
        return $this->bodyFormat;
    }

    public function setBodyFormat(string $bodyFormat = 'json'): self
    {
        $this->bodyFormat = $bodyFormat;

        return $this;
    }

    public function getXttpCookie(): HandlesXttpCookies
    {
        return $this->cookies;
    }

    public function getCookiesArray(): array
    {
        return $this->cookies->all();
    }

    public function withMock(MockHandler $mockHandler): self
    {
        $this->stack = HandlerStack::create($mockHandler);

        return $this;
    }

    public function withHistory(array &$container): self
    {
        $this->middleware[] = Middleware::history($container);

        return $this;
    }

    public function withStack(HandlerStack $handlerStack): self
    {
        $this->stack = $handlerStack;

        return $this;
    }

    public function makeStack(): self
    {
        foreach ($this->middleware as $middleware) {
            $this->stack->push($middleware);
        }

        return $this;
    }

    public function getStack(): HandlerStack
    {
        return $this->stack;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): ClientInterface
    {
        $this->makeStack();

        $localOptions = ['handler' => $this->stack];

        $guzzleOptions = array_merge($localOptions, $this->options);

        return new Client($guzzleOptions);
    }
}
