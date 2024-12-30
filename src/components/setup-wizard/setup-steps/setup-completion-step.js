import React from 'react';
import { Button } from '@/components/ui/button';
import { CheckCircle, ArrowLeft } from 'lucide-react';

const SetupCompletionStep = ({
  step,
  setStep
}) => {
  return (
    <div className="text-center space-y-6">
      <CheckCircle className="mx-auto h-20 w-20 text-green-500" />
      <h2 className="text-2xl font-bold">Setup Complete!</h2>
      <p>Your COD Funnel Booster is now ready to use. Start creating amazing funnels!</p>

      <div className="flex justify-between">
        <Button variant="outline" onClick={() => setStep(step - 1)}>
          <ArrowLeft className="mr-2 h-4 w-4" />
          Previous
        </Button>
        <Button 
          onClick={() => window.location.href = window.codFunnelWizard.dashboard_url}
        >
          Go to Dashboard
        </Button>
      </div>
    </div>
  );
};

export default SetupCompletionStep;