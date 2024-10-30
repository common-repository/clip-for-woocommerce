
import { decodeEntities } from '@wordpress/html-entities';

const settings = window.wc.wcSettings.getSetting( 'wc_clip_data', {} );
const label = window.wp.htmlEntities.decodeEntities( settings.title ) || window.wp.i18n.__( 'Clip Gateway', 'wc_clip' );

/**
 * Content component
 */
const Description = () => {
    return (
        <>
            <p>{ decodeEntities( settings.description || '' ) }</p>
            {settings.banner_enabled === 'yes' && <img src={ settings.banner_clip } />}
        </>
    );
};

const Title = () => {
	return (
        <>
            <p>{ decodeEntities( settings.title || '' ) }&nbsp;</p>
            <img
                src={ settings.icon_clip }
            />
        </>
        
	)
};

const Block_Gateway = {
    name: 'wc_clip',
	label: <Title />,
    content: <Description />,
	edit: <Description />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( Block_Gateway );