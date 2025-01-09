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
      const { buisinessInfo, geoConfig } = window.codFunnelConfigManager.storeConfig.data;
      
      if (buisinessInfo && geoConfig) {
        setFormData({
          buisinessName: buisinessInfo.buisinessName || '',
          buisinessEmail: buisinessInfo.buisinessEmail || '',
          buisinessCountry: buisinessInfo.buisinessCountry || '',
          buisinessState: buisinessInfo.buisinessState || '',
          buisinessCity: buisinessInfo.buisinessCity || '',
          buisinessAddress: buisinessInfo.buisinessAdress || '',
          buisinessCurrency: buisinessInfo.buisinessCurrency || '',
          sellOption: geoConfig.sellOption || 'all',
          specificCountries: geoConfig.specificCountries || [],
          excludedCountries: geoConfig.excludedCountries || [],
        });
      }
    }
  }, []);

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    // Clear any existing errors when user makes changes
    if (parentError) setParentError(null);
  };

  const validateFormData = () => {
    const requiredFields = [
      'buisinessName',
      'buisinessEmail',
      'buisinessCountry',
      'buisinessCity',
      'buisinessAddress',
      'buisinessCurrency',
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

      const response = await fetch(`${window.codFunnelConfigManager.restUrl}/store-settings`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.codFunnelConfigManager.nonce
        },
        body: JSON.stringify({
          ...formData,
          specificCountries: formData.sellOption === 'specific' ? formData.specificCountries : [],
          excludedCountries: formData.sellOption === 'all_except' ? formData.excludedCountries : [],
        })
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
              __(Business Information)
            </TabsTrigger>
            <TabsTrigger 
              value="cfb-config"
              className="rounded-md px-4 py-2.5 text-sm font-medium transition-all
                        data-[state=active]:bg-background dark:data-[state=active]:bg-background/10
                        data-[state=active]:text-foreground dark:data-[state=active]:text-foreground
                        data-[state=active]:shadow-sm
                        text-muted-foreground dark:text-muted-foreground"
              >
              Store Configurations
            </TabsTrigger>
          </TabsList>
        </div>

        <div className="flex-1 overflow-y-auto px-4 modern-scroll">
          <TabsContent value="cfb-info" className="mt-2 space-y-8">
            <div className="grid grid-cols-2 gap-8">
              {/* Form Fields */}
              <div className="space-y-6">
                <div className="space-y-2">
                  <Label className="text-sm font-medium text-foreground">
                    Store Name
                  </Label>
                  <Input
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
                    Business Email
                  </Label>
                  <Input
                    id="buisinessEmail"
                    type="email"
                    value={formData.buisinessEmail}
                    onChange={(e) => handleInputChange('buisinessEmail', e.target.value)}
                    className="h-10 px-3 py-2 bg-background dark:bg-background/5 dark:text-slate-200
                              border border-input dark:border-input/20
                              text-foreground placeholder:text-muted-foreground/60
                              rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                              hover:border-primary/30 transition-colors"
                    placeholder="Enter business email"
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
                        {window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.allCountries[formData.buisinessCountry]}
                      </SelectValue>
                    </SelectTrigger>
                    <SelectContent className="select-modern-content">
                      <div className="max-h-[300px] overflow-y-auto modern-scroll">
                        {Object.entries(window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.allCountries || {}).map(([code, country]) => (
                          <SelectItem 
                            key={code} 
                            value={country}
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
                  <Select
                    value={formData.buisinessState}
                    onValueChange={(value) => handleInputChange('buisinessState', value)}
                  >
                    <SelectTrigger id="states" className="h-10 px-3 py-2 bg-background dark:bg-background/5
                            border border-input dark:border-input/20
                            text-foreground placeholder:text-muted-foreground/60
                            rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30
                            hover:border-primary/30 transition-colors">
                      <SelectValue placeholder="Select a State" className="text-muted-foreground">
                        { formData.buisinessCountry && window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.states[formData.buisinessState]}
                      </SelectValue>
                    </SelectTrigger>
                    <SelectContent className="select-modern-content">
                    <div className="max-h-[300px] overflow-y-auto modern-scroll">
                      {Object.entries(window.codFunnelConfigManager?.storeConfig?.data?.geoConfig?.states || {}).map(([code, state]) => (
                        <SelectItem key={code} value={state} className="select-modern-item">
                          {state}
                        </SelectItem>
                      ))}
                      </div>
                    </SelectContent>
                    
                  </Select>
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
              <CODFunnelBoosterGeoConfig/>
            </div>
          </TabsContent>
        </div>

        <div className="sticky bottom-0 p-4 bg-background/80 backdrop-blur-sm border-t dark:border-slate-700 mt-auto">
          {parentError && (
            <Alert variant="destructive" className="mb-4">
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>{parentError}</AlertDescription>
            </Alert>
          )}

          <div className="flex items-center justify-between gap-4">
            <Button
              variant="outline"
              onClick={() => setStep(step - 1)}
              disabled={isLoading}
              className="button-modern"
            >
              <ArrowLeft className="mr-2 h-4 w-4" /> Previous
            </Button>
            <Button 
              onClick={handleSubmit} 
              disabled={isLoading}
              className="button-modern"
            >
              {isLoading ? (
                <>
                  <Loader className="mr-2 h-4 w-4 animate-spin" /> Saving...
                </>
              ) : (
                <>
                  Next <ArrowRight className="ml-2 h-4 w-4" />
                </>
              )}
            </Button>
          </div>
        </div>
      </Tabs>
    </div>
  );
};

export default InitialConfigurationStep;