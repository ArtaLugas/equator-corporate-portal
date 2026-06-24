<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale from the URL prefix and configures URL generation
 * so every generated link stays in the current language.
 *
 * URL strategy:
 *   - default locale (en) is served unprefixed: /services        (SEO canonical)
 *   - other locales are prefixed:               /id/services
 *   - an explicit default prefix (/en/...) 301-redirects to the canonical URL
 *
 * The global default URL::defaults(['locale' => '']) is set in AppServiceProvider
 * so positional route() calls (e.g. route('news.show', $slug)) keep working even
 * outside this group; here we only override it to a non-default locale.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $default = config('locales.default');
        $supported = array_keys(config('locales.supported', []));
        $locale = $request->route('locale');

        // Canonical redirect: the default locale must not be prefixed.
        if ($locale === $default) {
            $path = ltrim(preg_replace(
                '#^'.preg_quote($default, '#').'(/|$)#',
                '',
                $request->path()
            ), '/');

            $query = $request->getQueryString();

            return redirect('/'.$path.($query ? '?'.$query : ''), 301);
        }

        // Unprefixed or unknown → default; a valid prefix → that locale.
        $locale = in_array($locale, $supported, true) ? $locale : $default;

        app()->setLocale($locale);

        // Keep generated URLs in the active language. Empty string = unprefixed
        // (default locale) and keeps positional route() arguments aligned.
        URL::defaults(['locale' => $locale === $default ? '' : $locale]);

        // Drop the locale param so controller actions bind {slug} etc. cleanly.
        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
