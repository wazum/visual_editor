import './reset-button.mjs';
import './translation-selector.mjs';
import './editable-input.mjs';
import './editable-rte.mjs';
import './editara-area-brick.mjs';
import './editara-save-button.mjs';
import './iframe-popup.mjs';
import {isDirectMode, sendMessage} from '../Shared/iframe-messaging.mjs';

if (window.location.hash === '#editara-close') {
    sendMessage('closeModal');
    // this closes the window as it was a _target="_blank" opened window from the edit button (eg: editable: link)
    window.close();
}

console.log('%cEditara is running!', 'color: green; font-size: 20px; font-weight: bold;');

const element = document.createElement('editara-save-button');
document.body.appendChild(element);

if (isDirectMode) {
    console.log('%cEditara: in direct mode', 'color: red; font-size: 16px;');
} else {
    console.log('%cEditara: in iframe mode', 'color: orange; font-size: 16px;');
}



setTimeout(() => {
    window.requestAnimationFrame(() => {
      // highlight('.editara-focus');
    });
}, 50);
