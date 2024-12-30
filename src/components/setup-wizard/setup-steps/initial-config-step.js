import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, ArrowRight, Loader, AlertCircle } from 'lucide-react';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { wp } from '../../../types/window.js';
import MultiCountrySelect from '@/components/ui/custom/multi-country-select';

const InitialConfigurationStep = ({
  step,
  setStep,
  storeConfig,
  setStoreConfig
}) => {
  // Debug logging
  console.log('InitialConfigurationStep mounted with:', { storeConfig });

  // Form state initialization
  const [formData, setFormData] = useState({
    storeName: '',
    storeEmail: '',
    storeAddress: '',
    storeCity: '',
    storeCountry: '',
    storeState: '',
    sellOption: 'all',
    selectedCountries: []
  });
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  // Load initial data from storeConfig
  useEffect(() => {
    if (storeConfig?.data?.storeInfo) {
      setFormData(prev => ({
        ...prev,
        ...storeConfig.data.storeInfo,
        sellOption: storeConfig.data?.geoService?.sellOption || 'all',
        selectedCountries: sellOption === 'specific' ? storeConfig.data?.geoService?.specificCountries : storeConfig.data?.geoService?.excludedCountries
      }));
    }
  }, [storeConfig]);

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);

    try {
      
      const response = await fetch(`${wp.codFunnelConfigManager.restUrl}/store-settings`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wp.codFunnelConfigManager.nonce  // Use X-WP-Nonce header
        },
        credentials: 'same-origin', // Important for cookies
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (!response.ok) {
        console.error('Server response:', data);
        throw new Error(data.message || 'Failed to save store settings');
      }

      // Update store config with new data
      setStoreConfig(prev => ({
        ...prev,
        data: data.data
      }));

      setStep(step + 1);
    } catch (err) {
      console.error('Store settings error:', err);
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  // Show loading state if no config available
  if (!storeConfig?.data) {
    return (
      <div className="flex items-center justify-center p-4">
        <Loader className="h-8 w-8 animate-spin" />
        <span className="ml-2">Loading store configuration...</span>
      </div>
    );
  }

  const { countries } = storeConfig.data.geoService;

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && (
        <Alert variant="destructive">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label htmlFor="storeName">Store Name</Label>
          <Input
            id="storeName"
            value={formData.storeName}
            onChange={(e) => setFormData(prev => ({...prev, storeName: e.target.value}))}
            required
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="storeEmail">Store Email</Label>
          <Input
            id="storeEmail"
            type="email"
            value={formData.storeEmail}
            onChange={(e) => setFormData(prev => ({...prev, storeEmail: e.target.value}))}
            required
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label htmlFor="storeAddress">Store Address</Label>
        <Input
          id="storeAddress"
          value={formData.storeAddress}
          onChange={(e) => setFormData(prev => ({...prev, storeAddress: e.target.value}))}
          required
        />
      </div>

      <div className="grid grid-cols-3 gap-4">
        <div className="space-y-2">
          <Label htmlFor="storeCity">City</Label>
          <Input
            id="storeCity"
            value={formData.storeCity}
            onChange={(e) => setFormData(prev => ({...prev, storeCity: e.target.value}))}
            required
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="storeCountry">Country</Label>
          <Select
            value={formData.storeCountry}
            onValueChange={(value) => setFormData(prev => ({...prev, storeCountry: value}))}
          >
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Select Country" />
            </SelectTrigger>
            <SelectContent>
              {Object.entries(countries).map(([code, name]) => (
                <SelectItem key={code} value={code}>
                  {name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
      <MultiCountrySelect/>

        </div>
      </div>

      <div className="space-y-2">
        <Label>Selling Countries Configuration</Label>
        <MultiCountrySelect 
          formData={formData}
          setFormData={setFormData}
        />
      </div>

      <div className="flex justify-between pt-4">
        <Button 
          type="button" 
          variant="outline" 
          onClick={() => setStep(step - 1)}
        >
          <ArrowLeft className="mr-2 h-4 w-4" />
          Back
        </Button>
        <Button 
          type="submit"
          disabled={isLoading}
        >
          {isLoading && <Loader className="mr-2 h-4 w-4 animate-spin" />}
          Continue
          <ArrowRight className="ml-2 h-4 w-4" />
        </Button>
      </div>
    </form>
  );
};

export default InitialConfigurationStep;