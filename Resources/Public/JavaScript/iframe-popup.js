import {css, html, LitElement} from 'lit';
import {classMap} from 'lit/directives/class-map.js';
/**
 * @extends {HTMLElement}
 */
export class IframePopup extends LitElement {
    static properties = {
        title: {type: String,},
        src: {type: String,},
        size: {type: String,},
    };


    static styles = css`
        button {
            cursor: pointer;
        }

        iframe {
            border: none;

            &.full {
                width: 95vw;
                height: 95vh;
            }

            &.large {
                width: 75vw;
                height: 95vh;
            }

            &.medium {
                width: 60vw;
                height: 85vh;
            }
        }
    `;

    constructor() {
        super();
        this.size = 'large';
    }

    _click(event) {
        event.preventDefault();
        if (window.parent === window) {
            // direct mode, just navigate
            window.location = this.src;
            return;
        }

        const message = {
            command: "editaraOpenModal",
            src: this.src + '%23editara-close',
            title: this.title,
            size: this.size,
        };
        top.postMessage(message, '*');
    }

    render() {
        return html`
            <button @click="${this._click}" title="${this.title}">
                <slot/>
            </button>
        `;
    }
}

customElements.define('iframe-popup', IframePopup);
