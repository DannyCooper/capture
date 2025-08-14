import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

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
		disableCoreStyles,
		inputColor,
		inputBackgroundColor,
		privacyPolicyTextColor,
	} = attributes;

	const blockProps = useBlockProps(
		{
			className: disableCoreStyles ? 'capture-form--no-core-styles' : ''
		}
	);

	const formStyles = {
		gap: `${fieldGap}rem`, 
		display: 'flex', 
		flexDirection: formLayout === 'inline' ? 'row' : 'column'	}

	const inputStyles = {
		color: inputColor,
		backgroundColor: inputBackgroundColor,
		flexGrow: 1,
	}

	const buttonStyles = {
		backgroundColor: buttonColor,
		color: buttonTextColor,
		border: 'none',
		cursor: 'pointer'
	};

	const privacyPolicyStyles = {
		color: privacyPolicyTextColor,
	}

	return (
		<div {...blockProps}>
			{emsConnectionId && ! selectedListId && (
				<div className="capture-form__error">
					{__('Please select a list for the selected EMS connection.', 'capture')}
				</div>
			)}	
			<div className="capture-form__preview">
				<div style={formStyles}>
					{showNameField && (
						<input
							type="text"
							id={blockProps.id + '-name'}
							className="capture-form__input"
							style={inputStyles}
							placeholder={__('First name', 'capture')}
							readOnly
						/>
					)}
					<input
						type="email"
						id={blockProps.id + '-email'}
						className="capture-form__input"
						style={inputStyles}
						placeholder={__('Email address', 'capture')}
						value={__('example@domain.com', 'capture')}
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
				</div>
				{showPrivacyPolicy && optionsFromAPI?.privacy_policy_text && (
					<RichText.Content 
						tagName="div"
						className="capture-form__privacy-policy"
						value={optionsFromAPI.privacy_policy_text}
						style={privacyPolicyStyles}
					/>
					)}
			</div>
		</div>
	);
} 