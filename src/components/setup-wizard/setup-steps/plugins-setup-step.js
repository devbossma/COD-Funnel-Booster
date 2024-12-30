import React, { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, CheckCircle, Loader, ArrowRight } from 'lucide-react';
import { wp } from '../../../types/window.js';

const PluginsSetupStep = ({
  step,
  setStep,
  pluginStatuses,
  setPluginStatuses,
  loadingPlugins,
  setLoadingPlugins,
  error,
  setError
}) => {
  useEffect(() => {
    checkPluginStatuses();
  }, []);

  const checkPluginStatuses = async () => {
    try {
      const statuses = {};
      for (const [slug, plugin] of Object.entries(wp.codFunnelDependencyManager.plugins)) {
        statuses[slug] = {
          isInstalled: plugin.installed,
          isActive: plugin.activated,
          name: plugin.name,
          minVersion: plugin.min_version
        };
      }
      setPluginStatuses(statuses);
    } catch (err) {
      setError('Failed to check plugin statuses');
    }
  };

  const installPlugin = async (slug) => {
    setLoadingPlugins(prev => ({ ...prev, [slug]: 'install' }));
    setError(null);
    
    try {
      const formData = new FormData();
      formData.append('action', 'install_required_plugin');
      formData.append('nonce', wp.codFunnelDependencyManager.nonce);
      formData.append('plugin', slug);

      const response = await fetch(wp.codFunnelDependencyManager.ajaxUrl, {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      
      if (data.success) {
        setPluginStatuses(prev => ({
          ...prev,
          [slug]: {
            ...prev[slug],
            isInstalled: true
          }
        }));
      } else {
        throw new Error(data.data || 'Failed to install plugin');
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoadingPlugins(prev => {
        const newLoadingPlugins = { ...prev };
        delete newLoadingPlugins[slug];
        return newLoadingPlugins;
      });
    }
  };

  const activatePlugin = async (slug) => {
    setLoadingPlugins(prev => ({ ...prev, [slug]: 'activate' }));
    setError(null);
    
    try {
      const formData = new FormData();
      formData.append('action', 'activate_required_plugin');
      formData.append('nonce', wp.codFunnelDependencyManager.nonce);
      formData.append('plugin', slug);

      const response = await fetch(wp.codFunnelDependencyManager.ajaxUrl, {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      
      if (data.success) {
        setPluginStatuses(prev => ({
          ...prev,
          [slug]: {
            ...prev[slug],
            isActive: true
          }
        }));
      } else {
        throw new Error(data.data || 'Failed to activate plugin');
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoadingPlugins(prev => {
        const newLoadingPlugins = { ...prev };
        delete newLoadingPlugins[slug];
        return newLoadingPlugins;
      });
    }
  };

  const renderPluginStatus = (slug, status) => {
    const isLoading = loadingPlugins[slug];

    return (
      <div key={slug} id={slug} className="flex items-center justify-between p-4 border rounded-lg mb-4">
        <div className="flex items-center space-x-4">
          {status.isActive ? (
            <CheckCircle className="text-green-500 h-6 w-6" />
          ) : (
            <AlertCircle className="text-amber-500 h-6 w-6" />
          )}
          <div>
            <h3 className="font-medium">{status.name}</h3>
            <p className="text-sm text-gray-500">Min version: {status.minVersion}</p>
          </div>
        </div>
        <div>
          {!status.isInstalled ? (
            <Button 
              onClick={() => installPlugin(slug)}
              disabled={isLoading}
              variant="secondary"
            >
              {isLoading === 'install' ? <Loader className="animate-spin mr-2" /> : null}
              Install
            </Button>
          ) : !status.isActive ? (
            <Button 
              onClick={() => activatePlugin(slug)}
              disabled={isLoading}
              variant="secondary"
            >
              {isLoading === 'activate' ? <Loader className="animate-spin mr-2" /> : null}
              Activate
            </Button>
          ) : (
            <span className="text-green-500 flex items-center">
              <CheckCircle className="h-4 w-4 mr-2" />
              Active
            </span>
          )}
        </div>
      </div>
    );
  };

  const allPluginsReady = Object.values(pluginStatuses).every(status => status.isActive);
  const anyPluginLoading = Object.keys(loadingPlugins).length > 0;

  return (
    <>
      {error && (
        <Alert variant="destructive" className="mb-6">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="space-y-6">
        {Object.entries(pluginStatuses).map(([slug, status]) => 
          renderPluginStatus(slug, status)
        )}
      </div>

      <div className="mt-6 flex justify-end">
        <Button
          onClick={() => setStep(step + 1)}
          disabled={!allPluginsReady || anyPluginLoading}
        >
          Continue
          <ArrowRight className="ml-2 h-4 w-4" />
        </Button>
      </div>
      
    </>
  );
};

export default PluginsSetupStep;