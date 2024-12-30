import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { toggleFullscreen } from '../../utils/fullscreen';

const FullscreenButton = () => {
    const [isFullscreen, setIsFullscreen] = useState(false);

    const handleToggleFullscreen = () => {
        const fullscreenEnabled = toggleFullscreen();
        setIsFullscreen(fullscreenEnabled);
    };

    return (
        
        <Button
            text={isFullscreen ? '<< WordPress Admin' : 'Full Screen >>'}
            onClick={handleToggleFullscreen}
            className="absolute bottom-0 left-0 z-50"
            variant="secondary"
        />
    );
};

export default FullscreenButton;
