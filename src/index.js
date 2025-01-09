import React from '@wordpress/element';
import { createRoot } from '@wordpress/element';
import SetupWizardApp from './components/setup-wizard';

const rootElement = document.getElementById('cod-funnel-booster-app');
if (rootElement) {
    createRoot(rootElement).render(
        <React.StrictMode>
            <SetupWizardApp />
        </React.StrictMode>
    );
}
