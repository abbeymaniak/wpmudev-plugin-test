import {
	createRoot,
	render,
	StrictMode,
	createInterpolateElement,
	useState,
	useEffect
} from "@wordpress/element";
import { Notice,Button, TextControl } from "@wordpress/components";
import { __, _x } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {useDispatch} from '@wordpress/data';
import { store as noticesStore } from "@wordpress/notices";

import "./scss/style.scss"

const domElement = document.getElementById( window.wpmudevPluginTest.dom_element_id );


const WPMUDEV_PluginTest = () => {

	const [clientid, setClientId] = useState('');
	const [clientSecret, setClientSecret] = useState('');
	const [isNoticeVisible, setIsNoticeVisible] = useState(false);
	const [saveTitle, setSaveTitle] = useState('Save');

	const { createErrorNotice,createSuccessNotice } = useDispatch(noticesStore);


	//Get saved data
	useEffect(() => {
			apiFetch({ path: "/wpmudev/v1/get_settings" })
				.then((data) => {

					setClientId(data.client_id || "");
					setClientSecret(data.client_secret || "");

				})
				.catch((error) => {
					console.error("Failed to fetch settings:", error);
					dispatch("core/notices").createNotice(
						"error",
						__("Failed to fetch settings.", "wpmudev-plugin-test"),
					);
				});
	}, []);

	const handleDismiss = () => {
		setIsNoticeVisible(false);
	};

    const handleClick = () => {

		if(clientid.length === 0){
			createErrorNotice('Client ID is required', {
				id: 'subtitle-required',
				isDismissible: true,
			});
			alert('Client ID is Required!!!');
			return;

		}

		if(clientSecret.length === 0){
			createErrorNotice('Client Secret is required', {
				id: 'subtitle-required',
				isDismissible: true,
			});
			alert('Client Secret is Required!!!');
			return;

		}

		setSaveTitle('Saving Credentials...');

		apiFetch({
			path: "/wpmudev/v1/auth/auth-url",
			method: "POST",
			data: {
				client_id: clientid,
				client_secret: clientSecret,
			},
		}).then((response) => {

			 createSuccessNotice(__("Google Credentials saved.", "wpmudev-plugin-test"));
			setIsNoticeVisible(true);
			setSaveTitle('save');

				alert(__("Settings saved", "wpmudev-plugin-test"));
			}).catch((error) => {

			createErrorNotice(__('Error saving settings', 'wpmudev-plugin-test'));
			setSaveTitle('save');
				alert(__("Error saving settings", "wpmudev-plugin-test"));

			});
    }

    return (
		<>
			{isNoticeVisible && (
				<div className="editor-notices">
					<div className="notice inline notice-success is-dismissible">
						<small>{__('Credentials Saved Successfully', 'wpmudev-plugin-test')}</small>
						<button type="button" className="notice-dismiss" onClick={handleDismiss}>
							<span className="screen-reader-text">Dismiss</span>
						</button>
					</div>
				</div>
			)}
			<div className="sui-header">
				<h1 className="sui-header-title">
					{__("Settings", "wpmudev-plugin-test")}
				</h1>
			</div>

			<div className="sui-box">
				<div className="sui-box-header">
					<h2 className="sui-box-title">
						{__("Set Google credentials", "wpmudev-plugin-test")}
					</h2>
				</div>

				<div className="sui-box-body">
					<div className="sui-box-settings-row">
						<TextControl
							help={createInterpolateElement(
								"You can get Client ID from <a>here</a>.",
								{
									a: (
										<a href="https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid"/>
									),
								},
							)}
							label={__("Client ID", "wpmudev-plugin-test")}
							value={clientid}
							onChange={(value) => setClientId(value)}
							required
							className="is-required"
						/>
					</div>

					<div className="sui-box-settings-row">
						<TextControl
							help={createInterpolateElement(
								"You can get Client Secret from <a>here</a>.",
								{
									a: (
										<a href="https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid"/>
									),
								},
							)}
							label={__("Client Secret", "wpmudev-plugin-test")}
							type="password"
							value={clientSecret}
							onChange={(value) => setClientSecret(value)}
							required
						/>
					</div>

					<div className="sui-box-settings-row">
							<span>
								Please use this url{" "}
								<em>{window.wpmudevPluginTest.returnUrl}</em> in your Google
								API's <strong>Authorized redirect URIs</strong> field
							</span>
					</div>
				</div>

				<div className="sui-box-footer">
					<div className="sui-actions-right">
						<Button variant="primary" onClick={handleClick}>
							{saveTitle}
						</Button>
					</div>
				</div>
			</div>

		</>
	);
}

if (createRoot) {
	createRoot(domElement).render(<StrictMode><WPMUDEV_PluginTest/></StrictMode>);
} else {
	render(<StrictMode><WPMUDEV_PluginTest/></StrictMode>, domElement);
}
