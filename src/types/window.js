/**
 * WordPress localized script data for our plugin
 * @typedef {Object} CodFunnelData
 * @property {Object} codFunnelDependencyManager
 * @property {Object.<string, {installed: boolean, activated: boolean, name: string, min_version: string}>} codFunnelDependencyManager.plugins
 * @property {string} codFunnelDependencyManager.nonce
 * @property {string} codFunnelDependencyManager.ajaxUrl
 * @property {Object} codFunnelConfigManager
 * @property {string} codFunnelConfigManager.restUrl
 * @property {string} codFunnelConfigManager.nonce
 * @property {Object} codFunnelConfigManager.data
 */

/**
 * @type {CodFunnelData}
 */
const wp = window;

export { wp };
