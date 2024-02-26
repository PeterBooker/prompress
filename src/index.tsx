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
			isActive: true,
			features: [],
			isStorageCompatLoaded: false,
			storageCompat: [],
			storage: '',
		}
	);

	const {
		isLoaded,
		isActive,
		features,

		isStorageCompatLoaded,
		storageCompat,
		storage,
	} = state;

	if ( null === features ) {
		setState({
			features: {
				options: true,
				posts: true,
				queries: true,
				requests: true,
				remote_requests: true,
				updates: true,
			}
		});

		return null;
	}

	useEffect( () => {
		api.loadPromise.then( () => {
			const settings = new api.models.Settings();

			if ( isLoaded === false ) {
				settings.fetch().then( ( response ) => {
					setState( {
						isLoaded: true,
						isActive: response[ 'prompress_option_active' ],
						features:  response[ 'prompress_option_features' ],
						// features: {
						// 	options:  response[ 'prompress_option_feature_options' ],
						// 	posts:  response[ 'prompress_option_feature_posts' ],
						// 	queries:  response[ 'prompress_option_feature_queries' ],
						// 	requests:  response[ 'prompress_option_feature_requests' ],
						// 	remote_requests:  response[ 'prompress_option_feature_remote_requests' ],
						// 	updates:  response[ 'prompress_option_feature_updates' ],
						// }
					} );
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
								isActive
									? 'Monitoring is active.'
									: 'Monitoring is not active.'
							}
							checked={ isActive }
							onChange={ () => {
								setState({ isActive: ! isActive });
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

						<p>The ability to toggle specific features on/off will be coming soon.</p>

						{ features && Object.keys(features).forEach((key) => {
							<h2>{key}{console.log(key, features[key])}</h2>
						}) }
					</div>
				</div>
			</div>
			<div className="prompress__save">
				<Button
					variant="primary"
					onClick={ () => {
						const settings = new api.models.Settings( {
							[ 'prompress_option_active' ]: isActive,
							[ 'prompress_option_features' ]: features,
						} );

						settings.save();

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
