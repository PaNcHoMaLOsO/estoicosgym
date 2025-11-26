<?php
/**
 * IDE Helper for Laravel Helper Functions
 * This file is for IDE autocomplete support only
 * @noinspection ALL
 * @noinspection PhpUnusedParameterInspection
 */

/**
 * Get the view factory instance
 * @param string|null $view
 * @param array $data
 * @param array $mergeData
 * @return \Illuminate\View\View|\Illuminate\View\Factory
 */
function view($view = null, $data = array(), $mergeData = array()) {}

/**
 * Get an instance of the current request.
 * @param string|null $key
 * @param mixed $default
 * @return \Illuminate\Http\Request|mixed|null
 */
function request($key = null, $default = null) {}

/**
 * Create a new redirect response.
 * @param string|null $to
 * @param int $status
 * @param array $headers
 * @param bool|null $secure
 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
 */
function redirect($to = null, $status = 302, $headers = array(), $secure = null) {}

/**
 * Return a new response from the application.
 * @param string $content
 * @param int $status
 * @param array $headers
 * @return \Illuminate\Http\Response
 */
function response($content = '', $status = 200, array $headers = array()) {}

/**
 * Get the application instance.
 * @param string|null $make
 * @param array $parameters
 * @return \Illuminate\Foundation\Application|mixed
 */
function app($make = null, $parameters = array()) {}

/**
 * Create a Carbon instance from a string or now
 * @param null $time
 * @param \DateTimeZone|string|null $tz
 * @return \Carbon\Carbon
 */
function now($time = null, $tz = null) {}

/**
 * Get configuration option
 * @param array|string|null $key
 * @param mixed $default
 * @return mixed
 */
function config($key = null, $default = null) {}

/**
 * Get an environment variable
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null) {}

/**
 * Get the evaluated view contents of the given view.
 * @param string $key
 * @param array $replace
 * @param string $locale
 * @return array|string|null
 */
function trans($key = null, $replace = array(), $locale = null) {}

/**
 * Translates the given message by choosing a plural form
 * @param string $key
 * @param int|array $count
 * @param array $replace
 * @param string $locale
 * @return string
 */
function trans_choice($key, $count = 1, array $replace = array(), $locale = null) {}

/**
 * Get a translated message by key or simple message
 * @param string $key
 * @param array $replace
 * @param string|null $locale
 * @return string|array|null
 */
function __($key = null, $replace = array(), $locale = null) {}

/**
 * Generate an asset URL
 * @param string $path
 * @param bool|null $secure
 * @return string
 */
function asset($path, $secure = null) {}

/**
 * Generate the URL to an application asset.
 * @param string $path
 * @param string|null $root
 * @param bool|null $secure
 * @return string
 */
function asset_path($path, $root = null, $secure = null) {}

/**
 * Generate a URL for an application route.
 * @param string $name
 * @param mixed $parameters
 * @param bool $absolute
 * @return string
 */
function route($name, $parameters = array(), $absolute = true) {}

/**
 * Get the current authenticated user.
 * @param string|null $guard
 * @return \Illuminate\Contracts\Auth\Authenticatable|null
 */
function auth($guard = null) {}

/**
 * Dispatch a job to the queue.
 * @param mixed $job
 * @return mixed
 */
function dispatch($job) {}

/**
 * Get a logger instance.
 * @param string|null $channel
 * @return \Psr\Log\LoggerInterface
 */
function logger($channel = null) {}

/**
 * Generate a CSRF token form field.
 * @return string
 */
function csrf_field() {}

/**
 * Generate a CSRF token.
 * @return string
 */
function csrf_token() {}

/**
 * Generate an HTML hidden input field containing the value of the CSRF token.
 * @return string
 */
function method_field($method) {}

/**
 * Get a cache instance.
 * @param string|array|null $name
 * @return \Illuminate\Contracts\Cache\Repository|mixed
 */
function cache($name = null) {}

/**
 * Return a JSON response.
 * @param mixed $data
 * @param int $status
 * @param array $headers
 * @param int $options
 * @return \Illuminate\Http\JsonResponse
 */
function json_response($data = null, $status = 200, array $headers = array(), $options = 0) {}

/**
 * Get a validator instance.
 * @param array $data
 * @param array $rules
 * @param array $messages
 * @param array $customAttributes
 * @return \Illuminate\Validation\Validator
 */
function validator($data = null, $rules = array(), $messages = array(), $customAttributes = array()) {}

/**
 * Get an instance of the filesystem disk.
 * @param string|null $name
 * @return \Illuminate\Filesystem\FilesystemAdapter|\Illuminate\Contracts\Filesystem\Factory
 */
function storage_path($path = '') {}

/**
 * Get the URL for a filesystem disk file.
 * @param string $path
 * @param string|null $disk
 * @return string
 */
function storage_url($path, $disk = null) {}

/**
 * Get the base path for the application.
 * @param string $path
 * @return string
 */
function base_path($path = '') {}

/**
 * Get the fully qualified class name for a given class.
 * @param string $class
 * @return string
 */
function class_basename($class) {}

/**
 * Die and dump the values.
 * @return void
 */
function dd(...$args) {}

/**
 * Dump the values.
 * @return \Symfony\Component\VarDumper\VarDumperInterface
 */
function dump(...$args) {}

/**
 * Get the full URL to the given path.
 * @param string $path
 * @param mixed $parameters
 * @param bool|null $secure
 * @return string
 */
function url($path = '', $parameters = array(), $secure = null) {}
