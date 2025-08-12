<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * Billing address fields for the `Registration` model.
 *
 * @phpstan-require-extends Registration
 */
trait BillingAddressTrait
{
    /**
     * @var bool
     */
    protected $separateBillingAddress = false;

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 80})
     */
    protected $billingCompany = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 80})
     */
    protected $billingFullName = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 40})
     */
    protected $billingStreetAddress = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 10})
     */
    protected $billingZipCode = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 40})
     */
    protected $billingCity = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 40})
     */
    protected $billingCountry = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 40})
     */
    protected $billingPhoneNumber = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 50})
     */
    protected $billingEmailAddress = '';

    public function hasSeparateBillingAddress(): bool
    {
        return $this->separateBillingAddress;
    }

    public function setSeparateBillingAddress(bool $separateBillingAddress): void
    {
        $this->separateBillingAddress = $separateBillingAddress;
    }

    public function getBillingCompany(): string
    {
        return $this->billingCompany;
    }

    public function setBillingCompany(string $billingCompany): void
    {
        $this->billingCompany = $billingCompany;
    }

    public function getBillingFullName(): string
    {
        return $this->billingFullName;
    }

    public function setBillingFullName(string $billingFullName): void
    {
        $this->billingFullName = $billingFullName;
    }

    public function getBillingStreetAddress(): string
    {
        return $this->billingStreetAddress;
    }

    public function setBillingStreetAddress(string $billingStreetAddress): void
    {
        $this->billingStreetAddress = $billingStreetAddress;
    }

    public function getBillingZipCode(): string
    {
        return $this->billingZipCode;
    }

    public function setBillingZipCode(string $billingZipCode): void
    {
        $this->billingZipCode = $billingZipCode;
    }

    public function getBillingCity(): string
    {
        return $this->billingCity;
    }

    public function setBillingCity(string $billingCity): void
    {
        $this->billingCity = $billingCity;
    }

    public function getBillingCountry(): string
    {
        return $this->billingCountry;
    }

    public function setBillingCountry(string $billingCountry): void
    {
        $this->billingCountry = $billingCountry;
    }

    public function getBillingPhoneNumber(): string
    {
        return $this->billingPhoneNumber;
    }

    public function setBillingPhoneNumber(string $billingPhoneNumber): void
    {
        $this->billingPhoneNumber = $billingPhoneNumber;
    }

    public function getBillingEmailAddress(): string
    {
        return $this->billingEmailAddress;
    }

    public function setBillingEmailAddress(string $billingEmailAddress): void
    {
        $this->billingEmailAddress = $billingEmailAddress;
    }
}
