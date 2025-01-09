import React, { createContext, useContext, useEffect, useState } from "react"

const ThemeProviderContext = createContext({ theme: "light", setTheme: () => null });

export function ThemeProvider({
  children,
  defaultTheme = "light",
  storageKey = "ui-theme",
}) {
  const [theme, setTheme] = useState(() => {
    try {
      return localStorage.getItem(storageKey) || defaultTheme;
    } catch {
      return defaultTheme;
    }
  });

  useEffect(() => {
    const root = window.document.documentElement;
    
    // Remove both classes first
    root.classList.remove('light', 'dark');
    
    // Add the current theme class
    root.classList.add(theme);

    // Store the preference
    try {
      localStorage.setItem(storageKey, theme);
      console.log('Theme changed to:', theme); // Debug log
    } catch (error) {
      console.error('Failed to save theme preference:', error);
    }
  }, [theme, storageKey]);

  const value = React.useMemo(
    () => ({
      theme,
      setTheme: (newTheme) => {
        setTheme(newTheme);
      },
    }),
    [theme]
  );

  return (
    <ThemeProviderContext.Provider value={value}>
      {children}
    </ThemeProviderContext.Provider>
  );
}

export const useTheme = () => {
  const context = useContext(ThemeProviderContext);
  if (!context) {
    throw new Error("useTheme must be used within a ThemeProvider");
  }
  return context;
};
