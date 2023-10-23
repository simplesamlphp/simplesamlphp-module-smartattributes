<?php

declare(strict_types=1);

namespace SimpleSAML\Module\smartattributes\Auth\Process;

use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\Error;

class SmartID extends \SimpleSAML\Auth\ProcessingFilter
{
    /**
     * Which attributes to use as identifiers?
     *
     * IMPORTANT: If you use the (default) attributemaps (twitter2name, facebook2name,
     * etc., be sure to comment out the entries that map xxx_targetedID to
     * eduPersonTargetedID, or there will be no way to see its origin any more.
     *
     * @var string[]
     */
    private array $candidates = [
        'eduPersonTargetedID',
        'eduPersonPrincipalName',
        'pairwise-id',
        'subject-id',
        'openid',
        'facebook_targetedID',
        'twitter_targetedID',
        'windowslive_targetedID',
        'linkedin_targetedID',
    ];

    /**
     * @var string The name of the generated ID attribute.
     */
    private string $id_attribute = 'smart_id';

    /**
     * Whether to append the AuthenticatingAuthority, separated by '!'
     * This only works when SSP is used as a gateway.
     * @var bool
     */
    private bool $add_authority = true;

    /**
     * Whether to prepend the CandidateID, separated by ':'
     * @var bool
     */
    private bool $add_candidate = true;

    /**
     * Whether a missing identifier is o.k.
     * @var bool
     */
    private bool $fail_if_empty = true;

    /**
     * @param array $config
     * @param mixed $reserved
     * @throws \Exception
     */
    public function __construct(array $config, $reserved)
    {
        parent::__construct($config, $reserved);

        if (array_key_exists('candidates', $config)) {
            Assert::isArray(
                $config['candidates'],
                'SmartID authproc configuration error: \'candidates\' should be an array.'
            );
            $this->candidates = $config['candidates'];
        }

        if (array_key_exists('id_attribute', $config)) {
            Assert::string(
                $config['id_attribute'],
                'SmartID authproc configuration error: \'id_attribute\' should be a string.'
            );
            $this->id_attribute = $config['id_attribute'];
        }

        if (array_key_exists('add_authority', $config)) {
            Assert::boolean(
                $config['add_authority'],
                'SmartID authproc configuration error: \'add_authority\' should be a boolean.'
            );
            $this->add_authority = $config['add_authority'];
        }

        if (array_key_exists('add_candidate', $config)) {
            Assert::boolean(
                $config['add_candidate'],
                'SmartID authproc configuration error: \'add_candidate\' should be a boolean.'
            );
            $this->add_candidate = $config['add_candidate'];
        }

        if (array_key_exists('fail_if_empty', $config)) {
            Assert::boolean(
                $config['fail_if_empty'],
                'SmartID authproc configuration error: \'fail_if_empty\' should be a boolean.'
            );
            $this->fail_if_empty = $config['fail_if_empty'];
        }
    }


    /**
     * @param array $attributes
     * @param array $request
     * @return string
     * @throws \SimpleSAML\Error\Exception
     */
    private function addID(array $attributes, array $request): string
    {
        $state = $request['saml:sp:State'];
        foreach ($this->candidates as $idCandidate) {
            if (isset($attributes[$idCandidate][0])) {
                if ($this->add_authority && count($state['saml:AuthenticatingAuthority']) > 0) {
                    $authority = end($state['saml:AuthenticatingAuthority']);
                    return ($this->add_candidate ? $idCandidate . ':' : '') . $attributes[$idCandidate][0] . '!' .
                        $authority;
                } else {
                    return ($this->add_candidate ? $idCandidate . ':' : '') . $attributes[$idCandidate][0];
                }
            }
        }

        /**
         * At this stage no usable id_candidate has been detected.
         */
        if ($this->fail_if_empty) {
            throw new Error\Exception('This service needs at least one of the following ' .
                'attributes to identity users: ' . implode(', ', $this->candidates) . '. Unfortunately not ' .
                'one of them was detected. Please ask your institution administrator to release one of ' .
                'them, or try using another identity provider.');
        } else {
            /**
             * Return an empty identifier,
             * missing id attribute must be handled by another authproc filter
             */
            return '';
        }
    }


    /**
     * Apply filter to add or replace attributes.
     *
     * Add or replace existing attributes with the configured values.
     *
     * @param array &$state  The current request
     */
    public function process(array &$state): void
    {
        Assert::keyExists($state, 'Attributes');

        $id = $this->addID($state['Attributes'], $state);

        if (!empty($id)) {
            $state['Attributes'][$this->id_attribute] = [$id];
        }
    }
}
