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
} from '@wordpress/components';

import {
	Fragment,
	render,
} from '@wordpress/element';

import { dispatch } from '@wordpress/data';

// @ts-ignore Missing type declarations.
import api from '@wordpress/api';

import Notices from './components/Notices';

import './index.scss';

function Settings() {
	const [ state, setState ] = useReducer(
		( s, a ) => ({ ...s, ...a }),
		{
			isLoaded: false,
			isActive: true,
			features: [],
		}
	);

	const {
		isLoaded,
		isActive,
		features,
	} = state;

	useEffect( () => {
		api.loadPromise.then( () => {
			const settings = new api.models.Settings();

			if ( isLoaded === false ) {
				settings.fetch().then( ( response ) => {
					setState( {
						isLoaded: true,
						isActive: response[ 'prompress_option_active' ],
						features: response[ 'prompress_option_features' ],
					} );
				} );
			}
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
						{/* <TextControl
							help={ __( 'The URL to your Meilisearch server.', 'prompress' ) }
							label={ __( 'Meilisearch URL', 'prompress' ) }
							onChange={ ( value ) => setState( { url: value } ) }
							value={ url }
						/> */}
					</div>
				</div>
				<div className="components-panel">
					<div className="components-panel__body is-opened">
						<h2 className="components-panel__body-title">{ __( 'Features', 'prompress' ) }</h2>

						<p>The ability to toggle specific features on/off will be coming soon.</p>
					</div>
				</div>
			</div>
			<div className="prompress__save">
				<Button
					isPrimary
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
