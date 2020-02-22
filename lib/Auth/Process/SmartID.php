<?php

namespace SimpleSAML\Module\smartattributes\Auth\Process;

use Webmozart\Assert\Assert;

class SmartID extends \SimpleSAML\Auth\ProcessingFilter
{
    /**
     * Which attributes to use as identifiers?
     *
     * IMPORTANT: If you use the (default) attributemaps (twitter2name, facebook2name,
     * etc., be sure to comment out the entries that map xxx_targetedID to
     * eduPersonTargetedID, or there will be no way to see its origin any more.
     *
     * @var array
     */
    private $candidates = [
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
    private $id_attribute = 'smart_id';

    /**
     * Whether to append the AuthenticatingAuthority, separated by '!'
     * This only works when SSP is used as a gateway.
     * @var bool
     */
    private $add_authority = true;

    /**
     * Whether to prepend the CandidateID, separated by ':'
     * @var bool
     */
    private $add_candidate = true;

    /**
     * Whether a missing identifier is o.k.
     */
    private $fail_if_empty = true;

    /**
     * @param array $config
     * @param mixed $reserved
     * @throws \Exception
     */
    public function __construct(array $config, $reserved)
    {
        parent::__construct($config, $reserved);

        if (array_key_exists('candidates', $config)) {
            $this->candidates = $config['candidates'];
            if (!is_array($this->candidates)) {
                throw new \Exception('SmartID authproc configuration error: \'candidates\' should be an array.');
            }
        }

        if (array_key_exists('id_attribute', $config)) {
            $this->id_attribute = $config['id_attribute'];
            if (!is_string($this->id_attribute)) {
                throw new \Exception('SmartID authproc configuration error: \'id_attribute\' should be a string.');
            }
        }

        if (array_key_exists('add_authority', $config)) {
            $this->add_authority = $config['add_authority'];
            if (!is_bool($this->add_authority)) {
                throw new \Exception('SmartID authproc configuration error: \'add_authority\' should be a boolean.');
            }
        }

        if (array_key_exists('add_candidate', $config)) {
            $this->add_candidate = $config['add_candidate'];
            if (!is_bool($this->add_candidate)) {
                throw new \Exception('SmartID authproc configuration error: \'add_candidate\' should be a boolean.');
            }
        }

        if (array_key_exists('fail_if_empty', $config)) {
            $this->fail_if_empty = $config['fail_if_empty'];
            if (!is_bool($this->fail_if_empty)) {
                throw new \Exception('SmartID authproc configuration error: \'fail_if_empty\' should be a boolean.');
            }
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
                if (($this->add_authority) && (isset($state['saml:AuthenticatingAuthority'][0]))) {
                    return ($this->add_candidate ? $idCandidate . ':' : '') . $attributes[$idCandidate][0] . '!' .
                        $state['saml:AuthenticatingAuthority'][0];
                } else {
                    return ($this->add_candidate ? $idCandidate . ':' : '') . $attributes[$idCandidate][0];
                }
            }
        }

        /**
         * At this stage no usable id_candidate has been detected.
         */
        if ($this->fail_if_empty) {
            throw new \SimpleSAML\Error\Exception('This service needs at least one of the following ' .
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
     * @param array &$request  The current request
     * @return void
     */
    public function process(array &$request): void
    {
        Assert::keyExists($request, 'Attributes');

        $id = $this->addID($request['Attributes'], $request);

        if (!empty($id)) {
            $request['Attributes'][$this->id_attribute] = [$id];
        }
    }
}
