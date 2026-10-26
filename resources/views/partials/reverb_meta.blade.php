@php
    $reverbClientHost = (string) config('reverb.client.host');
    $reverbClientPort = (int) config('reverb.client.port');
    $reverbClientScheme = (string) config('reverb.client.scheme');
    $loopbackHosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1'];
    $configuredForLoopback = in_array(strtolower($reverbClientHost), $loopbackHosts, true);
    $requestIsLocal = in_array(strtolower(request()->getHost()), $loopbackHosts, true);

    if ($configuredForLoopback && !$requestIsLocal) {
        $reverbClientHost = request()->getHost();
        $reverbClientScheme = request()->isSecure() ? 'https' : 'http';
        $reverbClientPort = request()->getPort();
    }
@endphp
@if(config('reverb.client.key'))
<meta name="reverb-key" content="{{ config('reverb.client.key') }}">
<meta name="reverb-host" content="{{ $reverbClientHost ?: request()->getHost() }}">
<meta name="reverb-port" content="{{ $reverbClientPort }}">
<meta name="reverb-scheme" content="{{ $reverbClientScheme }}">
<meta name="reverb-path" content="{{ config('reverb.client.path') }}">
@endif
