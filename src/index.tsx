/**
 * External Imports.
 */
import { createRoot } from "react-dom/client";

import {
	useEffect,
	useReducer,
	useState,
} from 'react';

/**
 * Internal Imports.
 */
import { __ } from '@wordpress/i18n';

import {
	Button,
	ClipboardButton,
	Icon,
	Placeholder,
	Spinner,
	TextControl,
	ToggleControl,
	RadioControl,
} from '@wordpress/components';

import {
	Fragment,
	render,
} from '@wordpress/element';

import { dispatch } from '@wordpress/data';

// @ts-ignore Missing type declarations.
import api from '@wordpress/api';

import apiFetch from '@wordpress/api-fetch';

import Notices from './components/Notices';

import './index.scss';

function Settings( props ) {
	const [ state, setState ] = useReducer(
		( s, a ) => ({ ...s, ...a }),
		{
			isLoaded: false,
			configTemplate: '',
			settings: {
				active: true,
				authentication: false,
				authType: '',
				token: '',
				headerKey: '',
				headerValue: '',
				storage: 'apc',
				features: {
					emails: true,
					errors: true,
					options: true,
					posts: true,
					queries: true,
					requests: true,
					remote_requests: true,
					updates: true,
					users: true,
				},
			},
		}
	);

	const {
		isLoaded,
		settings,
		configTemplate,
	} = state;

	const {
		active,
		authentication,
		authType,
		token,
		headerKey,
		headerValue,
		features,
	} = settings;

	useEffect( () => {
		api.loadPromise.then( () => {

			if ( false === isLoaded ) {
				const initialSettings = props.settings;

				if (initialSettings) {
					setState( {
						isLoaded: true,
						settings: {
							active: initialSettings['active'],
							authentication: initialSettings['authentication'],
							authType: initialSettings['authType'],
							token: initialSettings['token'],
							headerKey: initialSettings['headerKey'],
							headerValue: initialSettings['headerValue'],
							features: initialSettings['features'],
						},
						configTemplate: props.configTemplate
					} );

					return;
				}

				const settings = new api.models.Settings();
				settings.fetch()
					.then( ( response ) => {
						if ( null !== response['prompress_settings'] ) {
							setState( {
								isLoaded: true,
								settings: {
									active: response['prompress_settings']['active'],
									authentication: response['prompress_settings']['authentication'],
									authType: response['prompress_settings']['authType'],
									token: response['prompress_settings']['token'],
									headerKey: response['prompress_settings']['headerKey'],
									headerValue: response['prompress_settings']['headerValue'],
									features: response['prompress_settings']['features'],
								},
							} );
						}
						setState( {
							isLoaded: true,
						} );
					} )
					.catch( ( error ) => {
						error.log(error);
					} );
			}
		} );
	}, [] );

	const [
		configHasCopied, setConfigHasCopied
	] = useState( false );

	function buildConfig( template, settings ) {
		var authReplacement = '';
		if (settings.authentication) {
			if (settings.authType === 'bearer') {
				authReplacement = "    authorization:\n"
					+ "      type: Bearer\n"
					+ "      credentials: '" + settings.token.replace("'", "''") + "'\n";
			} else if (settings.authType === 'api-key') {
				authReplacement = "    http_headers:\n      "
					+ settings.headerKey
					+ ":\n        values: ['" + settings.headerValue.replace("'", "''") + "']\n";
			}
		}

		return template.replace("%auth%\n", authReplacement);
	}

	useEffect( () => {
		apiFetch( {
			method: 'GET',
			path:  '/prompress/v1/storage/compatibility',
		} ).then( ( response ) => {
			setState({
				isStorageCompatLoaded: true,
				storageCompat: response
			});
		} ).catch( ( error ) => {
			error.log(error);
		} );
	}, [] );

	if ( ! isLoaded ) {
		return (
			<Placeholder>
				<Spinner />
			</Placeholder>
		);
	}

	const configText = buildConfig( configTemplate, settings );

	return (
		<Fragment>
			<div className="prompress__header">
				<div className="prompress__container">
					<div className="prompress__title">
						<h1>{ __( 'PromPress Settings', 'prompress' ) } <Icon icon="admin-plugins" /></h1>
					</div>
				</div>
			</div>
			<div className="prompress__main">
				<div className="components-panel">
					<div className="components-panel__body is-opened">
						<h2 className="components-panel__body-title">{ __( 'General', 'prompress' ) }</h2>
						<p>You can control whether monitoring is active or inactive globally.</p>
						<ToggleControl
							label="Active"
							help={
								active
									? 'Monitoring is active.'
									: 'Monitoring is not active.'
							}
							checked={ active }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										active: ! active,
									}
								});
							} }
						/>
						<Button
							variant="secondary"
							onClick={ () => {
								apiFetch( {
									method: 'GET',
									path:  '/prompress/v1/storage/wipe',
								} ).then( () => {
									dispatch( 'core/notices' ).createNotice(
										'success',
										__( 'Storage wiped', 'prompress' ),
										{
											type: 'snackbar',
											isDismissible: true,
										}
									);
								} ).catch( ( error ) => {
									dispatch( 'core/notices' ).createNotice(
										'success',
										__( 'Failed to wipe storage', 'prompress' ),
										{
											type: 'snackbar',
											isDismissible: true,
										}
									);
								} );
							} }
						>{ __( 'Wipe Storage', 'prompress' ) }</Button>
					</div>
				</div>

				<div className="components-panel">
					<div className="components-panel__body is-opened">
						<h2 className="components-panel__body-title">{ __( 'REST API', 'prompress' ) }</h2>
						<p>Enable authentication for the metrics endpoint.</p>
						<ToggleControl
							label={__('Require Authentication', 'prompress')}
							help={
								authentication
									? 'Authentication is required.'
									: 'Authentication is not required.'
							}
							checked={authentication}
							onChange={ () => {
								setState({
									settings: {
										...settings,
										authentication: ! authentication,
									}
								});
							} }
						/>
						{ authentication && (
							<>
								<div style={{display: 'flex', flex: '150px auto', gap: '16px'}}>
									<div style={{display: 'flex', gap: '16px', width: '150px'}}>
										<RadioControl
											label={__('Authentication Type', 'prompress')}
											selected={authType}
											options={[
												{label: 'Bearer', value: 'bearer'},
											]}
											onChange={(value) => {
												setState({
													settings: {
														...settings,
														authType: value,
													}
												});
											}}
										/>
									</div>
									<div style={{flexGrow: 1, gap: '16px'}}>
										<TextControl
											label={__('Bearer Token', 'prompress')}
											value={token}
											disabled={authType !== 'bearer'}
											onChange={(value) => {
												setState({
													settings: {
														...settings,
														token: value,
													}
												});
											}}
											help={__('Set a bearer token which must be sent by Prometheus to access the metrics endpoint.', 'prompress')}
										/>
									</div>
								</div>

								<div style={{display: 'flex', gap: '16px'}}>
									<div style={{display: 'flex', width: '150px'}}>
										<RadioControl
											selected={authType}
											options={[
												{label: 'Api-Key', value: 'api-key'},
											]}
											onChange={(value) => {
												setState({
													settings: {
														...settings,
														authType: value,
													}
												});
											}}
										/>
									</div>
									<div style={{flex: 1}}>
										<TextControl
											label={__('Header Key', 'prompress')}
											value={headerKey}
											disabled={authType !== 'api-key'}
											onChange={(value) => {
												setState({
													settings: {
														...settings,
														headerKey: value,
													}
												});
											}}
											help={__('Set a header name.', 'prompress')}
										/>
									</div>
									<div style={{flex: 1, gap: '16px'}}>
										<TextControl
											label={__('Header Value', 'prompress')}
											value={headerValue}
											disabled={authType !== 'api-key'}
											onChange={(value) => {
												setState({
													settings: {
														...settings,
														headerValue: value,
													}
												});
											}}
											help={__('And a secret value.', 'prompress')}
										/>
									</div>
								</div>
							</>
						)}
					</div>
				</div>

				<div className="components-panel">
					<div className="components-panel__body is-opened">
						<h2 className="components-panel__body-title">{ __( 'Features', 'prompress' ) }</h2>

						<p>Toggle the following features on/off to control what is being monitored.</p>

						<ToggleControl
							label={__('Emails', 'prompress')}
							help={__('Track the number of emails sent.', 'prompress')}
							checked={ features.emails }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											emails: ! features.emails
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Errors', 'prompress')}
							help={__('Track the number of errors thrown.', 'prompress')}
							checked={ features.errors }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											errors: ! features.errors
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Options', 'prompress')}
							help={__('Track the number of options.', 'prompress')}
							checked={ features.options }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											options: ! features.options
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Posts', 'prompress')}
							help={__('Track the number of posts (with post type).', 'prompress')}
							checked={ features.posts }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											posts: ! features.posts
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Queries', 'prompress')}
							help={__('Track the number and length of database queries. Note: The `SAVEQUERIES` constant must be set and true.', 'prompress')}
							checked={ features.queries }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											queries: ! features.queries
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Requests', 'prompress')}
							help={__('Track the number and length of requests.', 'prompress')}
							checked={ features.requests }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											requests: ! features.requests
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Remote Requests', 'prompress')}
							help={__('Track the number and length of remote requests.', 'prompress')}
							checked={ features.remote_requests }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											remote_requests: ! features.remote_requests
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Updates', 'prompress')}
							help={__('Track the number of plugin and theme updates available.', 'prompress')}
							checked={ features.updates }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											updates: ! features.updates
										}
									}
								});
							} }
						/>

						<ToggleControl
							label={__('Users', 'prompress')}
							help={__('Track the number of users (with role).', 'prompress')}
							checked={ features.users }
							onChange={ () => {
								setState({
									settings: {
										...settings,
										features: {
											...features,
											users: ! features.users
										}
									}
								});
							} }
						/>

					</div>
				</div>

				{ configTemplate && (
					<div className="components-panel">
						<div className="components-panel__body is-opened">
							<h2 className="components-panel__body-title">
								{__( 'Prometheus Config', 'prompress' )}
							</h2>
							<p>
								<div>
									<ClipboardButton
										className={ 'components-button is-secondary' }
										style={{ float: 'right' }}
										text={ configText }
										onCopy={ () => setConfigHasCopied(true) }
										onFinishCopy={ () => setConfigHasCopied( false ) }
									>
										{ configHasCopied
											? __( 'Copied!', 'prompress' )
											: __( 'Copy Config', 'prompress' )
										}
									</ClipboardButton>
								</div>
                                {__( 'This is what your config could look like.', 'prompress' )}
							</p>

							<pre># prometheus.yaml</pre>
							<pre>{ configText }</pre>
						</div>
					</div>
				)}
			</div>
			<div className="prompress__save">
				<Button
					variant="primary"
					onClick={ () => {
						const updatedSettings = new api.models.Settings( {
							[ 'prompress_settings' ]: settings,
						} );

						updatedSettings.save();

						dispatch( 'core/notices' ).createNotice(
							'success',
							__( 'Settings Saved', 'prompress' ),
							{
								type: 'snackbar',
								isDismissible: true,
							}
						);
					} }
				>{ __( 'Save', 'prompress' ) }</Button>
			</div>
			<div className="prompress__notices">
				<Notices />
			</div>
		</Fragment>
	);
}

document.addEventListener( 'DOMContentLoaded', () => {
	const elem = document.getElementById( 'prompress-plugin-settings' );

	if ( elem ) {
        const root = createRoot( elem );
        const prompressData = window['prompress'] || {};
		root.render(
			<Settings
                settings={ prompressData['settings'] }
                configTemplate={ prompressData['configTemplate'] }
            />,
		);
	}
} );
