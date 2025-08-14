<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Registration;

use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * Billing address fields for the `Registration` model.
 *
 * @phpstan-require-extends Registration
 */
trait BillingAddressTrait
{
    /**
     * @Validate("StringLength", options={"maximum": 80})
     */
    protected string $billingCompany = '';

    /**
     * @Validate("StringLength", options={"maximum": 80})
     */
    protected string $billingFullName = '';

    /**
     * @Validate("StringLength", options={"maximum": 40})
     */
    protected string $billingStreetAddress = '';

    /**
     * @Validate("StringLength", options={"maximum": 10})
     */
    protected string $billingZipCode = '';

    /**
     * @Validate("StringLength", options={"maximum": 40})
     */
    protected string $billingCity = '';

    /**
     * @Validate("StringLength", options={"maximum": 40})
     */
    protected string $billingCountry = '';

    /**
     * @Validate("StringLength", options={"maximum": 40})
     */
    protected string $billingPhoneNumber = '';

    /**
     * @Validate("StringLength", options={"maximum": 50})
     */
    protected string $billingEmailAddress = '';

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
