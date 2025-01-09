import React, { useEffect } from '@wordpress/element';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, CheckCircle, Loader, ArrowRight } from 'lucide-react';

const PluginItem = ({ plugin, status, onInstall, onActivate, isLoading }) => (
  <div className="flex items-center justify-between p-6 border border-border/50 dark:border-border/30 
                  rounded-xl bg-background/50 dark:bg-background/20 backdrop-blur-sm 
                  shadow-sm hover:shadow-md transition-all duration-200">
    <div className="flex items-center space-x-4">
      {status.isActive ? (
        <div className="rounded-full bg-primary/10 dark:bg-primary/20 p-2">
          <CheckCircle className="text-primary h-5 w-5" />
        </div>
      ) : (
        <div className="rounded-full bg-amber-500/10 dark:bg-amber-400/20 p-2">
          <AlertCircle className="text-amber-500 dark:text-amber-400 h-5 w-5" />
        </div>
      )}
      <div className="space-y-1">
        <h4 className="font-medium text-foreground">{status.name}</h4>
        <p className="text-sm text-muted-foreground">
          Version required: {status.minVersion}
        </p>
      </div>
    </div>
    
    <div>
      {!status.isInstalled ? (
        <Button
          onClick={() => onInstall(plugin, status.file, status.name)}
          disabled={isLoading}
          className="bg-secondary hover:bg-secondary/90 text-secondary-foreground"
        >
          {isLoading === "install" ? (
            <>
              <Loader className="animate-spin mr-2 h-4 w-4" />
              Installing...
            </>
          ) : (
            "Install"
          )}
        </Button>
      ) : !status.isActive ? (
        <Button
          onClick={() => onActivate(plugin, status.file, status.name)}
          disabled={isLoading}
          className="bg-primary/90 hover:bg-primary text-primary-foreground"
        >
          {isLoading === "activate" ? (
            <>
              <Loader className="animate-spin mr-2 h-4 w-4" />
              Activating...
            </>
          ) : (
            "Activate"
          )}
        </Button>
      ) : (
        <span className="flex items-center px-3 py-1 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary">
          <CheckCircle className="h-4 w-4 mr-2" />
          Active
        </span>
      )}
    </div>
  </div>
);

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
        for (const [slug, plugin] of Object.entries(window.codFunnelDependencyManager.plugins)) {
            statuses[slug] = {
            isInstalled: plugin.installed,
            isActive: plugin.activated,
            name: plugin.name,
            minVersion: plugin.min_version,
            file: plugin.file
            };
        }
        setPluginStatuses(statuses);
        } catch (err) {
        setError('Failed to check plugin statuses');
        }
    };

    const installPlugin = async (slug, plugin, name) => {
        setLoadingPlugins(prev => ({ ...prev, [slug]: 'install' }));
        setError(null);
        
        try {
            const formData = new FormData();
            formData.append('action', 'wp_ajax_install_plugin'); // Changed to match WP core action
            formData.append('slug', slug); // Plugin slug is passed directly
            formData.append('_ajax_nonce', window.codFunnelDependencyManager.nonce);
            const response = await fetch(window.codFunnelDependencyManager.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: "same-origin",
                timeout: 30000
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
                // After successful installation, activate the plugin
                await activatePlugin(slug, plugin, name);
            } else {
                throw new Error(data.data?.errorMessage || 'Failed to install plugin');
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

    const activatePlugin = async (slug, plugin, pluginName) => {
        setLoadingPlugins((prev) => ({ ...prev, [slug]: "activate" }));
        setError(null);
    
        try {
            const formData = new FormData();
            formData.append("action", "wp_ajax_activate_plugin");
            formData.append("_ajax_nonce", window.codFunnelDependencyManager.nonce);
            formData.append("slug", slug);
            formData.append("plugin", plugin);
            formData.append("name", pluginName);
        
            const response = await fetch(window.codFunnelDependencyManager.ajaxUrl, {
                method: "POST",
                body: formData,
                credentials: "same-origin",
            });
        
            const data = await response.json();
        
            if (data.success) {
                setPluginStatuses((prev) => ({
                ...prev,
                [slug]: {
                    ...prev[slug],
                    isActive: true,
                },
                }));
            } else {
                throw new Error(data.data?.errorMessage || "Failed to activate plugin");
            }
            } catch (err) {
            setError(err.message);
            } finally {
            setLoadingPlugins((prev) => {
                const newLoadingPlugins = { ...prev };
                delete newLoadingPlugins[slug];
                return newLoadingPlugins;
            });
            }
        };
        
        const allPluginsReady = Object.values(pluginStatuses).every(
            (status) => status.isActive
        );
        const anyPluginLoading = Object.keys(loadingPlugins).length > 0;
        
        return (
            <div className="space-y-8">
            {error && (
                <Alert variant="destructive" className="bg-destructive/10 dark:bg-destructive/20 border-none">
                <AlertCircle className="h-4 w-4 text-destructive" />
                <AlertDescription className="text-destructive">{error}</AlertDescription>
                </Alert>
            )}

            <div className="space-y-4">
                {Object.entries(pluginStatuses).map(([slug, status]) =>
                <PluginItem 
                    key={slug}
                    plugin={slug}
                    status={status}
                    onInstall={installPlugin}
                    onActivate={activatePlugin}
                    isLoading={loadingPlugins[slug]}
                />
                )}
            </div>

            <div className="flex justify-end pt-4">
                <Button
                onClick={() => setStep(step + 1)}
                disabled={!allPluginsReady || anyPluginLoading}
                className="button-modern"
                >
                {anyPluginLoading ? (
                    <>
                    <Loader className="animate-spin mr-2 h-4 w-4" />
                    Processing...
                    </>
                ) : (
                    <>
                    Continue
                    <ArrowRight className="ml-2 h-4 w-4" />
                    </>
                )}
                </Button>
            </div>
            </div>
        );
    };
    
    export default PluginsSetupStep;