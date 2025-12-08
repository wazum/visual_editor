import {css, html, LitElement} from 'lit';

/**
 * @extends {HTMLElement}
 */
export class EditaraColumn extends LitElement {
  static properties = {
    target: {type: Number},
    colPos: {type: Number},
    sys_language_uid: {type: Number},
  };

  constructor() {
    super();
    // observe child changes and rerender this component
    const observer = new MutationObserver(() => {
      this.requestUpdate();
    });
    observer.observe(this, {childList: true});
  }

  render() {
    const newContentUrl = window.editaraInfo.newContentUrl
      .replace('__COL_POS__', this.colPos)
      .replace('__SYS_LANGUAGE_UID__', this.sys_language_uid)
      .replace('__UID_PID__', this.target);

    const editaraColumnHasChild = this.children.length > 0;
    const addButton = html`<div class="center">
      <iframe-popup title="new Content" src="${newContentUrl}" type="ajax">
        <editara-icon name="actions-document-add" width="2em"></editara-icon>
        Create new Content
      </iframe-popup>
    </div>`;
    return html`
      <div class="editara-column">
        ${(editaraColumnHasChild ? '' : addButton)}
        <editara-drop-zone
          table="tt_content"
          target="${this.target}"
          colPos="${this.colPos}"
          sys_language_uid="${this.sys_language_uid}"
        ></editara-drop-zone>
        <slot></slot>
      </div>
    `;
  }

  static styles = css`
    :host {
    }
    
    .editara-column {
      position: relative;
    }
    
    .center {
      display: flex;
      justify-content: center;
      align-items: center;
    }
  `;
}

customElements.define('editara-column', EditaraColumn);
