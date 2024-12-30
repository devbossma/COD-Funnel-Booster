const BUTTON_STYLES = `
  .wp-fullscreen-control {
    position: fixed;
    bottom: 5px;
    left: 0px;
    z-index: 9999;
  }

  .wp-fullscreen-button {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 8px 16px !important;
    transition: all 0.2s ease-in-out !important;
    background-color: rgba(255, 255, 255, 0.9) !important;
  }

  .wp-fullscreen-button:hover {
    transform: translateY(-1px) !important;
    background-color: rgba(255, 255, 255, 1) !important;
  }

  .wp-fullscreen-button svg {
    width: 20px;
    height: 20px;
    color: var(--text);
  }

  .wp-fullscreen-button-text {
    font-size: 14px;
    font-weight: 500;
    color: var(--text);
  }

  .wp-fullscreen-tooltip {
    position: absolute;
    bottom: 0;
    left: 10;
    background-color: var(--dark-text);
    color: var(--text);
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    animation: fadeInUp 0.2s ease-out;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
`;

const FULLSCREEN_STYLES = `
  html, body {
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow-y: auto !important;
  }

  #wpwrap {
    min-height: 100% !important;
    position: relative !important;
  }

  #wpadminbar, 
  #adminmenumain {
    display: none !important;
  }

  #wpcontent {
    margin-left: 0 !important;
    padding-left: 0 !important;
  }

  .post-cfb-container {
    margin: 0 !important;
    transform: none !important;
    min-height: 100vh;
    min-width: 100vw;
    border-radius: 0px !important;
  }
  #wpfooter {
    display: none !important;
}
`;

export const toggleFullscreen = () => {
    const styleId = 'cod-funnel-fullscreen-styles';
    const buttonStyleId = 'cod-funnel-button-styles';
    const existingStyle = document.getElementById(styleId);
    const existingButtonStyle = document.getElementById(buttonStyleId);

    if (existingStyle) {
        existingStyle.remove();
        existingButtonStyle?.remove();
        return false;
    }

    // Add fullscreen styles
    const style = document.createElement('style');
    style.id = styleId;
    style.textContent = FULLSCREEN_STYLES;
    document.head.appendChild(style);

    // Add button styles
    const buttonStyle = document.createElement('style');
    buttonStyle.id = buttonStyleId;
    buttonStyle.textContent = BUTTON_STYLES;
    document.head.appendChild(buttonStyle);

    // Add ESC key listener
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            document.getElementById(styleId)?.remove();
            document.getElementById(buttonStyleId)?.remove();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);

    return true;
};
