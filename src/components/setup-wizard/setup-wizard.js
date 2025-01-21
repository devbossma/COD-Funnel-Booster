import React, { useState, useEffect } from "@wordpress/element";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import PluginsSetupStep from "./setup-steps/plugins-setup-step";
import SetupCompletionStep from "./setup-steps/setup-completion-step";
import InitialConfigurationStep from "./setup-steps/initial-config-step";
import ErrorBoundary from "./error-boundary";
import {cn}  from "@/lib/utils";
import StepHeroBanner from './components/step-hero-banner';

const SetupWizard = () => {
  console.log('SetupWizard rendering'); // Add this debug log

  const [step, setStep] = useState(1);
  const [pluginStatuses, setPluginStatuses] = useState({});
  const [loadingPlugins, setLoadingPlugins] = useState({});
  const [error, setError] = useState(null);
  const [buisinessInfo, setbuisinessInfo] = useState({});
  const [storeConfig, setStoreConfig] = useState({});

  // Configuration for wizard steps
  const wizardSteps = [
    {
      id: 1,
      title: "Plugin Dependencies",
      description:
        "Install and activate required plugins for COD Funnel Booster",
      component: PluginsSetupStep,
    },
    {
      id: 2,
      title: "Initial Configuration",
      description: "Configure basic settings for your funnel builder",
      component: InitialConfigurationStep,
    },
    {
      id: 3,
      title: "Completion",
      description: "Setup is complete! Start creating your funnels",
      component: SetupCompletionStep,
    },
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
    buisinessInfo,
    setbuisinessInfo,
    storeConfig,
    setStoreConfig,
  };

  const CurrentStepComponent = wizardSteps.find((s) => s.id === step).component;
  const currentStep = wizardSteps.find((s) => s.id === step);

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-secondary/20 to-background">
      <StepHeroBanner
        step={step}
        title={currentStep.title}
        description={currentStep.description}
        totalSteps={wizardSteps.length}
      />
      
      <div className="relative z-10 container mx-auto px-6 py-8">
        <Card className="card-modern">
          <CardContent className="p-8">
            <ErrorBoundary>
              <CurrentStepComponent {...sharedProps} />
            </ErrorBoundary>
          </CardContent>

          <CardFooter className="px-8 py-6 border-t border-border/50 dark:border-border/30 bg-muted/5">
            <div className="w-full flex items-center justify-between">
              <div className="flex space-x-2">
                {wizardSteps.map((wizardStep) => (
                  <div
                    key={wizardStep.id}
                    className={cn(
                      "w-2.5 h-2.5 rounded-full transition-all duration-300",
                      step === wizardStep.id 
                        ? "bg-blue-500 scale-110" 
                        : "bg-slate-200"
                    )}
                  ></div>
                ))}
              </div>
            </div>
          </CardFooter>
        </Card>
      </div>
    </div>
  );
};

export default SetupWizard;
