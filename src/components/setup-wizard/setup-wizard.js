import React, { useState, useEffect } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import PluginsSetupStep from './setup-steps/plugins-setup-step';
import SetupCompletionStep from './setup-steps/setup-completion-step';
import InitialConfigurationStep from './setup-steps/initial-config-step';
import ErrorBoundary from './error-boundary';

const SetupWizard = () => {
  const [step, setStep] = useState(1);
  const [pluginStatuses, setPluginStatuses] = useState({});
  const [loadingPlugins, setLoadingPlugins] = useState({});
  const [error, setError] = useState(null);
  const [configuration, setConfiguration] = useState({
    storeName: '',
    primaryNiche: '',
    funnelType: ''
  });
  const [storeConfig, setStoreConfig] = useState({
    isWooCommerceReady: false,
    data: null,
    error: null
  });

  useEffect(() => {
    // Get store configuration from localized data
    // @ts-ignore
    if (window.codFunnelConfigManager) {
      // @ts-ignore
      setStoreConfig(window.codFunnelConfigManager.storeConfig);
    }
  }, []);

  // Configuration for wizard steps
  const wizardSteps = [
    {
      id: 1,
      title: 'Plugin Dependencies',
      description: 'Install and activate required plugins for COD Funnel Booster',
      component: PluginsSetupStep
    },
    {
      id: 2,
      title: 'Initial Configuration',
      description: 'Configure basic settings for your funnel builder',
      component: InitialConfigurationStep
    },
    {
      id: 3,
      title: 'Completion',
      description: 'Setup is complete! Start creating your funnels',
      component: SetupCompletionStep
    }
  ];

  // Shared state and methods passed to child components
  const sharedProps = {
    step,
    setStep,
    pluginStatuses,
    setPluginStatuses,
    loadingPlugins,
    setLoadingPlugins,
    error,
    setError,
    configuration,
    setConfiguration,
    storeConfig,
    setStoreConfig
  };

  const CurrentStepComponent = wizardSteps.find(s => s.id === step).component;

  return (
    <div className=" z-50 min-h-screen  flex flex-row md:bg-gradient-to-tl from-purple-700 to-purple-950  backdrop-blur-3xl py-12 px-4">
      
      <
// @ts-ignore
      Card className="min-w-full md:min-w-80 mx-auto basis-1/2 bg-slate-100 shadow-sm backdrop-blur-3xl contrast-100 grid grid-cols-1 gap-1 content-between  ">
        <
// @ts-ignore
        CardHeader className=" mt-6 text-center">
          <
// @ts-ignore
          CardTitle>{wizardSteps.find(s => s.id === step).title}</CardTitle>
          <
// @ts-ignore
          CardDescription>
            {wizardSteps.find(s => s.id === step).description}
          </CardDescription>
        </CardHeader>
        <
// @ts-ignore
        CardContent>
          <ErrorBoundary>
            <CurrentStepComponent {...sharedProps} />
          </ErrorBoundary>
        </CardContent>
        <
// @ts-ignore
        CardFooter className="flex items-center justify-center">
        <div className="px-4 flex items-center justify-center">
          <div className="flex space-x-2">
            {wizardSteps.map(wizardStep => (
              <div 
                key={wizardStep.id} 
                className={`h-2 w-16 rounded-full ${
                  step === wizardStep.id ? 'bg-green-400' : 'bg-gray-300'
                }`}
              ></div>
            ))}
          </div>
        </div>
        </CardFooter>
      </Card>
    </div>
  );

};

export default SetupWizard;