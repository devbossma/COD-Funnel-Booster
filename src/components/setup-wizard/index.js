import React from '@wordpress/element';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from '@/providers/theme-provider';
import SetupWizard from './setup-wizard';
import '@/styles/globals.css';
import './index.css';

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('cod-funnel-wizard-root');
    if (container) {
        const root = createRoot(container);
        root.render(
                <ThemeProvider defaultTheme="light" storageKey="cod-funnel-theme">
                    <div className="min-h-screen bg-background text-foreground transition-colors">
                        <SetupWizard />
                    </div>
                </ThemeProvider>
        );
    } else {
        console.error('Setup wizard root element not found');
    }
});

export default SetupWizard;