import React, { useState, useEffect } from '@wordpress/element';
import { Button } from '@/components/ui/button';
import {__} from '@wordpress/i18n'
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, ArrowRight, Loader, AlertCircle } from 'lucide-react';
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup, SelectLabel  } from '@/components/ui/select'
import CODFunnelBoosterGeoConfig from '@/components/ui/custom/cod-funnel-booster-geo-config';
import {cn} from '@/lib/utils';

const StateInputField = ({ states, value, onChange, country }) => {
    const hasStates = states && Object.keys(states).length > 0;
    const customStates = window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.customStates || {};

    if (hasStates) {
        return (
            <Select
                value={value}
                onValueChange={onChange}
            >
                <SelectTrigger id="states" className="h-10 px-3 py-2 bg-background dark:bg-background/5
                        border border-input dark:border-input/20
                        text-foreground placeholder:text-muted-foreground/60
                        rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                        hover:border-primary/30 transition-colors">
                    <SelectValue placeholder="Select a State" className="text-muted-foreground">
                        {value && states[value]}
                    </SelectValue>
                </SelectTrigger>
                <SelectContent className="select-modern-content">
                    <div className="max-h-[300px] overflow-y-auto modern-scroll">
                        {Object.entries(states).map(([code, state]) => (
                            <SelectItem key={code} value={code} className="select-modern-item">
                                {state}
                            </SelectItem>
                        ))}
                    </div>
                </SelectContent>
            </Select>
        );
    }

    return (
        <Input
            id="buisinessState"
            value={value}
            onChange={(e) => {
                const newValue = e.target.value;
                if (value !== newValue) {
                    onChange(newValue);
                }
            }}
            placeholder={`Enter state/region for ${country}`}
            className="h-10 px-3 py-2 bg-background dark:bg-background/5
                    border border-input dark:border-input/20
                    text-foreground placeholder:text-muted-foreground/60
                    rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                    hover:border-primary/30 transition-colors"
        />
    );
};

const InitialConfigurationStep = ({
  step,
  setStep,
  storeConfig,
  setStoreConfig,
  error: parentError,
  setError: setParentError
}) => {
  const [formData, setFormData] = useState({
    buisinessName: '',
    buisinessEmail: '',
    buisinessCountry: '',
    buisinessState: '',
    buisinessCity: '',
    buisinessAddress: '',
    buisinessCurrency: '',
    sellOption: 'all',
    specificCountries: [],
    excludedCountries: [],
  });

  const [isLoading, setIsLoading] = useState(false);
  console.log('gonfigurations: ', window.codFunnelConfigManager?.storeConfig?.data );

  useEffect(() => {
    if (
      window.codFunnelConfigManager?.storeConfig?.isWooCommerceReady &&
      window.codFunnelConfigManager?.storeConfig?.data
    ) {
      const { buisinessInfo, geoConfig } = window.codFunnelConfigManager?.storeConfig?.data;
      
      if (buisinessInfo && geoConfig) {
        const hasStates = geoConfig.states && Object.keys(geoConfig.states).length > 0;
        const customState = !hasStates && geoConfig.customStates?.[buisinessInfo.buisinessCountry];
        
        setFormData(prev => ({
          ...prev,
          buisinessName: buisinessInfo.buisinessName || '',
          buisinessEmail: buisinessInfo.buisinessEmail || '',
          buisinessCountry: buisinessInfo.buisinessCountry || '',
          ...(hasStates || customState ? { buisinessState: hasStates ? buisinessInfo.buisinessState : customState } : {}),
          buisinessCity: buisinessInfo.buisinessCity || '',
          buisinessAddress: buisinessInfo.buisinessAdress || '',
          buisinessCurrency: buisinessInfo.buisinessCurrency || '',
          sellOption: geoConfig.sellOption || 'all',
          specificCountries: geoConfig.specificCountries || [],
          excludedCountries: geoConfig.excludedCountries || [],
        }));
      }
    }
  }, []);

// Update the states fetching useEffect
useEffect(() => {
    if (formData.buisinessCountry) {
        const countryCode = formData.buisinessCountry;
        const currentState = formData.buisinessState; // Store current state before fetching
        const currentCustomState = window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.customStates?.[countryCode];

        fetch(`${window.codFunnelConfigManager.restUrl}/states/${countryCode}`, {
            headers: {
                'X-WP-Nonce': window.codFunnelConfigManager.nonce,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Received states:', data);
            
            if (window.codFunnelConfigManager?.storeConfig?.data?.geoConfig) {
                const newStates = data.states || {};
                window.codFunnelConfigManager.storeConfig.data.geoConfig.states = newStates;
                
                // Determine if we should keep the current state
                if (Object.keys(newStates).length > 0) {
                    // If country has predefined states and current state is valid, keep it
                    if (currentState && Object.keys(newStates).includes(currentState)) {
                        return; // Keep the current state
                    }
                    // Reset state if it's not valid for the new country
                    setFormData(prev => ({
                        ...prev,
                        buisinessState: ''
                    }));
                } else if (currentCustomState) {
                    // Use custom state if available for countries without predefined states
                    setFormData(prev => ({
                        ...prev,
                        buisinessState: currentCustomState
                    }));
                } else {
                    // Reset state for countries without states
                    setFormData(prev => ({
                        ...prev,
                        buisinessState: ''
                    }));
                }
            }
        })
        .catch(error => {
            console.error('Error fetching states:', error);
            setParentError('Failed to fetch states for selected country');
        });
    }
}, [formData.buisinessCountry]);

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));

    if (parentError) setParentError(null);
  };

  const validateFormData = () => {
    const requiredFields = [
      'buisinessName',
      'buisinessEmail',
      'buisinessCountry',
      'buisinessState',
      'buisinessCity',
      'buisinessAddress',
      'sellOption'
    ];

    const missingFields = requiredFields.filter(field => !formData[field]);
    if (missingFields.length > 0) {
      throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
    }
  };

  const handleSubmit = async () => {
    setIsLoading(true);
    try {
        validateFormData();

        const countryCode = formData.buisinessCountry;
        const availableStates = window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.states || {};
        let stateCode = formData.buisinessState;

        const sellOptionData = {
            ...formData,
            buisinessCountry: countryCode,
            buisinessState: stateCode,
            specificCountries: formData.sellOption === 'specific' ? formData.specificCountries : [],
            excludedCountries: formData.sellOption === 'all_except' ? formData.excludedCountries : [],
        };

        const response = await fetch(`${window.codFunnelConfigManager.restUrl}/store-settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.codFunnelConfigManager.nonce
            },
            body: JSON.stringify(sellOptionData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || __('Failed to save store settings'));
        }

        const data = await response.json();
        setStoreConfig(data.data);
        setStep(step + 1);
    } catch (err) {
        setParentError(err.message);
        console.error('Error saving store settings:', err);
    } finally {
        setIsLoading(false);
    }
};

  return (
    <div className="relative w-full min-h-[400px] max-h-[400px] flex flex-col">
      <Tabs defaultValue="cfb-info" className="flex-1 flex flex-col">
        <div className="sticky top-0 z-10 bg-transparent mb-6">
          <TabsList className="w-full grid grid-cols-2 gap-2 p-1 bg-muted/50 dark:bg-muted/20 rounded-lg">
            <TabsTrigger 
              value="cfb-info" 
            className="rounded-md px-4 py-2.5 text-sm font-medium transition-all
                      data-[state=active]:bg-background dark:data-[state=active]:bg-background/10
                      data-[state=active]:text-foreground dark:data-[state=active]:text-foreground
                      data-[state=active]:shadow-sm
                      text-muted-foreground dark:text-muted-foreground"
            >
              {__('Business Information', 'cod-funnel-booster')}
            </TabsTrigger>
            <TabsTrigger 
              value="cfb-config"
              className="rounded-md px-4 py-2.5 text-sm font-medium transition-all
                        data-[state=active]:bg-background dark:data-[state=active]:bg-background/10
                        data-[state=active]:text-foreground dark:data-[state=active]:text-foreground
                        data-[state=active]:shadow-sm
                        text-muted-foreground dark:text-muted-foreground"
              >
              {__('Store Configurations', 'cod-funnel-booster')}
            </TabsTrigger>
          </TabsList>
        </div>

        <div className="flex-1 overflow-y-auto px-4 modern-scroll pb-3">
          <TabsContent value="cfb-info" className="mt-2 space-y-8">
            <div className="grid grid-cols-2 gap-8">
              {/* Form Fields */}
              <div className="space-y-6">
                <div className="space-y-2">
                  <Label Label htmlFor="buisinessName" className="text-sm font-medium text-foreground">
                    Store Name
                  </Label>
                  <Input
                    id="buisinessName"
                    value={formData.buisinessName}
                    onChange={(e) => handleInputChange('buisinessName', e.target.value)}
                    className="h-10 px-3 py-2 bg-background dark:bg-background/5
                              border border-input dark:border-input/20
                              text-foreground placeholder:text-muted-foreground/60
                              rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                              hover:border-primary/30 transition-colors"
                    placeholder="Enter store name"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="buisinessEmail" className="text-sm font-medium text-foreground">
                    {__('Business Email', 'cod-funnel-booster')}
                  </Label>
                  <Input
                    id="buisinessEmail"
                    type="email"
                    value={formData.buisinessEmail}
                    onChange={(e) => handleInputChange('buisinessEmail', e.target.value)}
                    className="form-input-base"
                    placeholder={__('Enter business email', 'cod-funnel-booster')}
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="buisinessCountry" className="text-sm font-medium text-foreground">
                    Country
                  </Label>
                  <Select
                    value={formData.buisinessCountry}
                    onValueChange={(value) => handleInputChange('buisinessCountry', value)}
                  >
                    <SelectTrigger id="buisinessCountry" className="h-10 px-3 py-2 bg-background dark:bg-background/5
                            border border-input dark:border-input/20
                            text-foreground placeholder:text-muted-foreground/60
                            rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                            hover:border-primary/30 transition-colors">
                      <SelectValue placeholder="Select a country" className="text-muted-foreground">
                        { formData.buisinessCountry && window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.allCountries[formData.buisinessCountry]}
                      </SelectValue>
                    </SelectTrigger>
                    <SelectContent className="select-modern-content">
                      <div className="max-h-[300px] overflow-y-auto modern-scroll">
                        {Object.entries(window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.allCountries || {}).map(([code, country]) => (
                          <SelectItem 
                            key={code} 
                            value={code} // Change this to use code instead of country name
                            className="select-modern-item"
                          >
                            {country}
                          </SelectItem>
                        ))}
                      </div>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              {/* Second Column */}
              <div className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="states" className="text-sm font-medium text-foreground">States</Label>
                  <StateInputField
                    states={window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.states || {}}
                    value={formData.buisinessState}
                    onChange={(value) => handleInputChange('buisinessState', value)}
                    country={formData.buisinessCountry}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="buisinessCity" className="text-sm font-medium text-foreground">City</Label>
                  <Input
                    id="buisinessCity"
                    value={formData.buisinessCity}
                    onChange={(e) => handleInputChange('buisinessCity', e.target.value)}
                    placeholder="Enter your store City"
                    className="h-10 px-3 py-2 bg-background dark:bg-background/5
                            border border-input dark:border-input/20
                            text-foreground placeholder:text-muted-foreground/60
                            rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                            hover:border-primary/30 transition-colors"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="storeBaseAddress" className="text-sm font-medium text-foreground">Buisness Address</Label>
                  <Input
                    id="buisinessAddress"
                    value={formData.buisinessAddress}
                    onChange={(e) => handleInputChange('buisinessAddress', e.target.value)}
                    placeholder="Enter your store address"
                    className="h-10 px-3 py-2 bg-background dark:bg-background/5
                            border border-input dark:border-input/20
                            text-foreground placeholder:text-muted-foreground/60
                            rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                            hover:border-primary/30 transition-colors"
                  />
                </div>
              </div>
            </div>
          </TabsContent>

          <TabsContent 
            value="cfb-config"
            className="mt-6 space-y-6"
          >
            <div className="grid gap-2">
              <CODFunnelBoosterGeoConfig
                countries={window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.allCountries || {}}
                sellOption={formData.sellOption}
                onSellOptionChange={(value) => handleInputChange('sellOption', value)}
                selectedCountries={
                  formData.sellOption === 'specific' 
                    ? formData.specificCountries 
                    : formData.sellOption === 'all_except'
                      ? formData.excludedCountries
                      : []
                }
                onSelectedCountriesChange={(countries) => {
                  handleInputChange(
                    formData.sellOption === 'specific' ? 'specificCountries' : 'excludedCountries',
                    countries
                  );
                }}
              />
            </div>
          </TabsContent>
        </div>

        <div className="sticky bottom-0 mt-auto">
          {/* Error Alert */}
          {parentError && (
            <div className="px-6 py-3 bg-destructive/5 dark:bg-destructive/10 border-t border-destructive/10 dark:border-destructive/20">
              <Alert variant="destructive" className="max-w-3xl mx-auto bg-transparent border-none">
                <div className="flex items-center gap-2">
                  <AlertCircle className="h-4 w-4 text-destructive" />
                  <AlertDescription className="text-destructive text-sm font-medium">
                    {parentError}
                  </AlertDescription>
                </div>
              </Alert>
            </div>
          )}

          {/* Actions Footer */}
          <div className="border-t border-border/50 dark:border-border/30 bg-gradient-to-b from-background/50 to-background dark:from-background/30 dark:to-background/50 backdrop-blur-lg">
            <div className="max-w-3xl mx-auto px-6 py-4">
              <div className="flex items-center justify-between gap-4">
                <Button
                  variant="outline"
                  onClick={() => setStep(step - 1)}
                  disabled={isLoading}
                  className="relative group px-6 py-2 border-border/50 dark:border-border/30 hover:border-primary/50 dark:hover:border-primary/30"
                >
                  <span className="flex items-center">
                    <ArrowLeft className="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Previous
                  </span>
                </Button>

                <Button 
                  onClick={handleSubmit} 
                  disabled={isLoading}
                  className="relative px-6 py-2 bg-primary/90 hover:bg-primary text-primary-foreground
                            shadow-lg shadow-primary/20 dark:shadow-primary/10
                            hover:shadow-primary/30 dark:hover:shadow-primary/20
                            transition-all duration-300 group"
                >
                  {isLoading ? (
                    <span className="flex items-center">
                      <Loader className="mr-2 h-4 w-4 animate-spin" />
                      Saving...
                    </span>
                  ) : (
                    <span className="flex items-center">
                      Save & Continue
                      <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </span>
                  )}
                </Button>
              </div>
            </div>
          </div>
        </div>
      </Tabs>
    </div>
  );
};

export default InitialConfigurationStep;