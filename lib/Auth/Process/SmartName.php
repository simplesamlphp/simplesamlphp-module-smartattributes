<?php

namespace SimpleSAML\Module\smartattributes\Auth\Process;

use Webmozart\Assert\Assert;

/**
 * Filter to set name in a smart way, based on available name attributes.
 *
 * @package SimpleSAMLphp
 */
class SmartName extends \SimpleSAML\Auth\ProcessingFilter
{
    /**
     * @param array $attributes
     * @return string|null
     */
    private function getFullName(array $attributes): ?string
    {
        if (isset($attributes['displayName'])) {
            return $attributes['displayName'][0];
        }

        if (isset($attributes['cn'])) {
            if (count(explode(' ', $attributes['cn'][0])) > 1) {
                return $attributes['cn'][0];
            }
        }

        if (isset($attributes['sn']) && isset($attributes['givenName'])) {
            return $attributes['givenName'][0] . ' ' . $attributes['sn'][0];
        }

        if (isset($attributes['cn'])) {
            return $attributes['cn'][0];
        }

        if (isset($attributes['sn'])) {
            return $attributes['sn'][0];
        }

        if (isset($attributes['givenName'])) {
            return $attributes['givenName'][0];
        }

        if (isset($attributes['eduPersonPrincipalName'])) {
            $localname = $this->getLocalUser($attributes['eduPersonPrincipalName'][0]);
            if (isset($localname)) {
                return $localname;
            }
        }

        return null;
    }


    /**
     * @param string $userid
     * @return string|null
     */
    private function getLocalUser(string $userid): ?string
    {
        if (strpos($userid, '@') === false) {
            return null;
        }
        $decomposed = explode('@', $userid);
        if (count($decomposed) === 2) {
            return $decomposed[0];
        }
        return null;
    }


    /**
     * Apply filter to add or replace attributes.
     *
     * Add or replace existing attributes with the configured values.
     *
     * @param array &$request  The current request
     */
    public function process(array &$request): void
    {
        Assert::keyExists($request, 'Attributes');

        $attributes = &$request['Attributes'];

        $fullname = $this->getFullName($attributes);

        if (isset($fullname)) {
            $request['Attributes']['smartname-fullname'] = [$fullname];
        }
    }
}
