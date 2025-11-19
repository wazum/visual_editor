import {css, html, LitElement} from 'lit';
import {onMessage, sendMessage} from '../Shared/iframe-messaging.mjs';

/**
 * @extends {HTMLElement}
 */
export class EditaraBackendSaveButton extends LitElement {
  static properties = {
    count: {type: Number, reflect: true},
    disabled: {type: Boolean, reflect: true},
    saving: {type: Boolean},
  };

  willUpdate(changedProperties) {
    this.disabled = this.saving === true || this.count === 0;
    /** @type {HTMLElement} */
    const e = this;
    if (this.disabled) {
      this.classList.remove('btn-primary');
      this.classList.add('btn-default');
    } else {
      this.classList.add('btn-warning');
      this.classList.remove('btn-default');
    }
  }

  constructor() {
    super();
    this.count = 0;
    this.saving = false;
    this.disabled = true;

    onMessage('updateChangesCount', (count) => {
      console.log('Update changes count:', count);
      this.count = count;
    });

    onMessage('onSave', () => {
      this.saving = true;
    });

    onMessage('saveEnded', () => {
      this.saving = false;
    });
    this.addEventListener('click', (e) => {
      e.preventDefault();
      sendMessage('doSave');
    })
  }

  render() {
    let s = '';
    if (this.count > 0) {
      const label = this.count === 1 ? 'change' : 'changes';
      s = html`<span class="changes-count">${this.count} ${label}</span>`;
    }
    if (this.saving) {
      s = html`<span class="saving-indicator">...</span>`;
    }
    return html`
      <slot></slot>${s}
    `;
  }


  static styles = css`
    :host {
    }
  `;
}

customElements.define('editara-backend-save-button', EditaraBackendSaveButton);
