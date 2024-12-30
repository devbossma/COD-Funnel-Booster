<?php

namespace Tests\Unit\Services\Geography;

use PHPUnit\Framework\TestCase;
use DevBossMa\CODFunnelBooster\Core\Services\Geography\WooCommerceGeoService;
use DevBossMa\CODFunnelBooster\Core\Exceptions\GeoServiceException;
use DevBossMa\CODFunnelBooster\Core\Contracts\CFBLoggerInterface;

class WooCommerceGeoServiceTest extends TestCase
{
    private $geoService;
    private $logger;
    private $wc_countries;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock WC_Countries
        $this->wc_countries = $this->createMock(\WC_Countries::class);
        
        // Setup mock countries data
        $mockCountries = [
            'US' => 'United States',
            'CA' => 'Canada'
        ];
        
        // Setup mock states data
        $mockStates = [
            'US' => [
                'CA' => 'California',
                'NY' => 'New York'
            ]
        ];

        // Configure WC_Countries mock
        $this->wc_countries
            ->method('get_countries')
            ->willReturn($mockCountries);
            
        $this->wc_countries
            ->method('get_states')
            ->willReturnCallback(function($country) use ($mockStates) {
                return $mockStates[$country] ?? [];
            });

        // Make mock available globally
        global $wc_countries_mock;
        $wc_countries_mock = $this->wc_countries;

        // Mock logger
        $this->logger = $this->createMock(CFBLoggerInterface::class);
        
        // Create service instance
        $this->geoService = new WooCommerceGeoService($this->logger);
    }

    public function test_get_countries_returns_array()
    {
        $countries = $this->geoService->get_countries();
        $this->assertIsArray($countries);
        $this->assertArrayHasKey('US', $countries);
        $this->assertEquals('United States', $countries['US']);
    }

    public function test_throws_exception_for_invalid_country_code()
    {
        $this->expectException(GeoServiceException::class);
        $this->expectExceptionCode(GeoServiceException::ERROR_INVALID_COUNTRY_CODE);
        
        $this->geoService->get_states_by_country_code('INVALID');
    }

    public function test_get_states_returns_array_for_valid_country()
    {
        $states = $this->geoService->get_states_by_country_code('US');
        $this->assertIsArray($states);
        $this->assertArrayHasKey('CA', $states);
        $this->assertEquals('California', $states['CA']);
    }

    public function test_validate_location_with_valid_country()
    {
        $result = $this->geoService->validate_location('US');
        $this->assertTrue($result);
    }

    public function test_validate_location_with_invalid_country()
    {
        $result = $this->geoService->validate_location('INVALID');
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clean up global mock
        global $wc_countries_mock;
        $wc_countries_mock = null;
    }
}
