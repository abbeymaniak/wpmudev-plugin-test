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
import { dispatch } from "@wordpress/data";

import "./scss/style.scss"

const domElement = document.getElementById( window.wpmudevPluginTest.dom_element_id );

console.log(domElement);

const WPMUDEV_PluginTest = () => {

	const [scanResult, setScanResult] = useState('');
	const [scanTitle, setScanTitle] = useState('Scan');



	const handleScanPosts = () => {
		setScanTitle('Scanning...');
		fetch('/wp-json/wpmudev/v1/scan_posts')
			.then(response => response.json())
			.then(data => {
				setScanResult(data.data);
				setScanTitle('Scan');

			})
			.catch(error => {
				console.error('Error fetching scan result:', error);
				setScanResult('Error occurred while scanning.');
			});
	}

    return (
			<>

				<div className="sui-header">
					<h1 className="sui-header-title">
						{__("Post Maintenance", "wpmudev-plugin-test")}
					</h1>
				</div>

				<div className="sui-box">
					<div className="sui-box-footer">
						<div className="sui-actions-right">
							<Button variant="primary" onClick={handleScanPosts}>
								{scanTitle}
							</Button>
						</div>
						<p className="scan-results">{scanResult}</p>
					</div>
				</div>

			</>
		);
}

if ( createRoot ) {
    createRoot( domElement ).render(<StrictMode><WPMUDEV_PluginTest/></StrictMode>);
} else {
    render( <StrictMode><WPMUDEV_PluginTest/></StrictMode>, domElement );
}
