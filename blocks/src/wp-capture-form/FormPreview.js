import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function FormPreview({ attributes, optionsFromAPI }) {
	const {
		emsConnectionId,
		selectedListId,
		formLayout,
		fieldGap,
		showNameField,
		showPrivacyPolicy,
		buttonColor,
		buttonTextColor,
		disableCoreStyles
	} = attributes;

	const blockProps = useBlockProps(
		{
			style: { 
				gap: `${fieldGap}rem`, 
				display: 'flex', 
				flexDirection: formLayout === 'inline' ? 'row' : 'column' 
		    },
			className: `capture-form capture-form--${formLayout} ${disableCoreStyles ? 'capture-form--no-core-styles' : ''}`
		}
	);

	const buttonStyles = {
		backgroundColor: buttonColor,
		color: buttonTextColor,
		border: 'none',
		cursor: 'pointer',
	};

	return (
		<div {...blockProps}>
			{emsConnectionId && ! selectedListId && (
				<div className="capture-form__error">
					{__('Please select a list for the selected EMS connection.', 'capture')}
				</div>
			)}
			{showNameField && (
				<input
					type="text"
					id={blockProps.id + '-name'}
					className="capture-form__input"
					placeholder={__('First name', 'capture')}
					readOnly
				/>
			)}
			<input
				type="email"
				id={blockProps.id + '-email'}
				className="capture-form__input"
				placeholder={__('Email address', 'capture')}
				readOnly
				/>
			
			<button
				type="button"
				className="capture-form__button"
				style={buttonStyles}
				onClick={(event) => event.preventDefault()}
			>
				{__('Subscribe', 'capture')}
			</button>
			{showPrivacyPolicy && optionsFromAPI?.privacy_policy_text && (
				<span dangerouslySetInnerHTML={{ __html: optionsFromAPI.privacy_policy_text }} className="capture-form__privacy-policy">
				</span>
			)}
		</div>
	);
} 