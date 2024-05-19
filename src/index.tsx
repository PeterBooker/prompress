/**
 * External Imports.
 */
import React from 'react';
import * as ReactDOMClient from 'react-dom/client';

import {
	useEffect,
	useReducer,
} from 'react';

/**
 * Internal Imports.
 */
import { __ } from '@wordpress/i18n';

import {
	Button,
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

function Settings() {
	const [ state, setState ] = useReducer(
		( s, a ) => ({ ...s, ...a }),
		{
			isLoaded: false,
			settings: {
				active: true,
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
		isActive,
		settings,
	} = state;

	const {
		active,
		storage,
		features,
	} = settings;

	useEffect( () => {
		api.loadPromise.then( () => {
			const settings = new api.models.Settings();

			if ( false === isLoaded ) {
				settings.fetch()
					.then( ( response ) => {
						console.log(response);
						if ( null !== response['prompress_settings'] ) {
							setState( {
								isLoaded: true,
								settings: {
									active: response['prompress_settings']['active'],
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
		render(
			<Settings />,
			elem
		);
	}
} );
