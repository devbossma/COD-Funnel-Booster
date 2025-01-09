import React from '@wordpress/element';
import { cn } from "@/lib/utils";
import { ThemeToggle } from '@/components/theme-toggle';

const StepHeroBanner = ({ step, title, description, totalSteps }) => {
    return (
        <div className="sticky top-0 z-20 bg-background/80 backdrop-blur-xl border-b border-border/50 dark:border-border/30">
        <div className="container max-w-4xl mx-auto px-6">
            <div className="py-6">
            <div className="flex justify-between items-center mb-6">
                {/* Enhanced Logo Section with better light/dark contrast */}
                <div className="flex items-center gap-3.5">
                <div className="relative group">
                    {/* Adjusted gradient glow effect for better light mode */}
                    <div className="absolute -inset-1 bg-gradient-to-r 
                                from-primary/40 via-accent/30 to-primary/40 
                                dark:from-primary/60 dark:via-accent/50 dark:to-primary/60
                                rounded-2xl blur-sm opacity-60 group-hover:opacity-100 
                                transition duration-300"></div>
                    {/* Logo container with better light mode bg */}
                    <div className="relative w-14 h-14 bg-slate-50 dark:bg-slate-900 rounded-xl 
                                flex items-center justify-center shadow-lg 
                                border border-slate-200 dark:border-slate-700">
                    {/* Logo text with separate light/dark gradients */}
                    <span className="text-lg font-bold 
                                    bg-gradient-to-br from-purple-600 via-primary  to-purple-600 dark:from-primary dark:via-accent dark:to-primary 
                                    bg-clip-text text-transparent 
                                    filter contrast-125 dark:contrast-100
                                    drop-shadow-[0_0.5px_0.5px_rgba(0,0,0,0.1)] dark:drop-shadow-[0_0.5px_0.5px_rgba(255,255,255,0.1)]">
                        COD
                    </span>
                    </div>
                </div>
                {/* Brand text with separate light/dark gradients */}
                <div className="flex flex-col -space-y-0.5">
                    <span className="text-xl font-bold 
                                bg-gradient-to-r from-purple-600 via-primary  to-purple-600 dark:from-primary dark:via-accent dark:to-primary 
                                bg-clip-text text-transparent 
                                filter contrast-125 dark:contrast-100
                                drop-shadow-[0_0.5px_0.5px_rgba(0,0,0,0.1)] dark:drop-shadow-[0_0.5px_0.5px_rgba(255,255,255,0.1)]">
                    Funnel
                    </span>
                    <span className="text-sm font-semibold text-foreground/90 dark:text-foreground/70">
                    Booster
                    </span>
                </div>
                </div>

                {/* Progress and Theme Toggle */}
                <div className="flex items-center gap-6">
                <div className="flex items-center gap-4">
                    <span className="text-sm font-medium text-muted-foreground">
                    Step {step} of {totalSteps}
                    </span>
                    <div className="w-32 h-1.5 bg-muted/30 dark:bg-muted/20 rounded-full overflow-hidden">
                    <div 
                        className="h-full bg-primary transition-all duration-500 ease-out"
                        style={{ width: `${(step / totalSteps) * 100}%` }}
                    />
                    </div>
                </div>
                <ThemeToggle />
                </div>
            </div>
            
            <div className="text-center space-y-2">
                <h1 className="text-2xl font-semibold text-foreground">
                {title}
                </h1>
                <p className="text-muted-foreground max-w-lg mx-auto text-sm">
                {description}
                </p>
            </div>
            </div>
        </div>
        </div>
    );
};

export default StepHeroBanner;
