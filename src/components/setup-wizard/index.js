import React from 'react';
import { createRoot } from 'react-dom/client';
import SetupWizard from './setup-wizard';
import './index.css';

document.addEventListener('DOMContentLoaded', () => {
    // Add debug logging
    console.log('Config Manager Data:', window.codFunnelConfigManager);
    
    const container = document.getElementById('cod-funnel-wizard-root');
    if (container) {
        const root = createRoot(container);
        root.render(<SetupWizard />);
    } else {
        console.error('Setup wizard root element not found');
    }
});

export default SetupWizard;