import React from 'react';
import { Moon, Sun } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/providers/theme-provider";

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();

  return (
    <Button
      variant="ghost"
      size="icon"
      onClick={() => setTheme(theme === "dark" ? "light" : "dark")}
      className="rounded-xl w-10 h-10 bg-background/50 hover:bg-background/80 border border-border/50 dark:border-border/30"
    >
      {theme === "dark" ? (
        <Sun className="h-5 w-5 text-primary transition-all rotate-0 scale-100" />
      ) : (
        <Moon className="h-5 w-5 text-primary transition-all rotate-0 scale-100" />
      )}
      <span className="sr-only">Toggle theme</span>
    </Button>
  );
}
