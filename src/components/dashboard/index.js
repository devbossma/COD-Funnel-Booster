import { createRoot } from '@wordpress/element';
// import { createRoot } from 'react-dom/client';
import { HashRouter as Router } from 'react-router-dom';
import Dashboard  from '@/components/dashboard';
import './index.css';

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Find the container element where we'll mount our React app
    const container = document.getElementById('cod-funnel-dashboard-root');
    const root = createRoot(container); 

    
    // Only render if we found the container
    // This prevents errors if the component is loaded on wrong pages
    if (container) {
        root.render( <Dashboard />, container );
    } else {
        console.warn('Setup Dashboard container not found in the DOM');
    }
});

// Export for potential reuse
export default Dashboard;