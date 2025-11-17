/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import {ClassicEditor as R} from "@ckeditor/ckeditor5-editor-classic";
import M from "@typo3/core/event/debounce-event.js";

const I = [{module: "@ckeditor/ckeditor5-block-quote", exports: ["BlockQuote"]}, {
  module: "@ckeditor/ckeditor5-essentials",
  exports: ["Essentials"],
}, {module: "@ckeditor/ckeditor5-find-and-replace", exports: ["FindAndReplace"]}, {
  module: "@ckeditor/ckeditor5-heading",
  exports: ["Heading"],
}, {module: "@ckeditor/ckeditor5-indent", exports: ["Indent", "IndentBlock"]}, {
  module: "@ckeditor/ckeditor5-link",
  exports: ["Link"],
}, {module: "@ckeditor/ckeditor5-list", exports: ["List"]}, {
  module: "@ckeditor/ckeditor5-paragraph",
  exports: ["Paragraph"],
}, {module: "@ckeditor/ckeditor5-clipboard", exports: ["PastePlainText"]}, {
  module: "@ckeditor/ckeditor5-paste-from-office",
  exports: ["PasteFromOffice"],
}, {module: "@ckeditor/ckeditor5-remove-format", exports: ["RemoveFormat"]}, {
  module: "@ckeditor/ckeditor5-table",
  exports: ["Table", "TableToolbar", "TableProperties", "TableCellProperties", "TableCaption"],
}, {module: "@ckeditor/ckeditor5-typing", exports: ["TextTransformation"]}, {
  module: "@ckeditor/ckeditor5-source-editing",
  exports: ["SourceEditing"],
}, {module: "@ckeditor/ckeditor5-alignment", exports: ["Alignment"]}, {
  module: "@ckeditor/ckeditor5-style",
  exports: ["Style"],
}, {module: "@ckeditor/ckeditor5-html-support", exports: ["GeneralHtmlSupport"]}, {
  module: "@ckeditor/ckeditor5-basic-styles",
  exports: ["Bold", "Italic", "Subscript", "Superscript", "Strikethrough", "Underline"],
}, {
  module: "@ckeditor/ckeditor5-special-characters",
  exports: ["SpecialCharacters", "SpecialCharactersEssentials"],
}, {module: "@ckeditor/ckeditor5-horizontal-line", exports: ["HorizontalLine"]}];

async function O(o, e, r, n = R) {
  const {
    importModules: s,
    removeImportModules: p,
    width: l,
    height: a,
    readOnly: t,
    debug: c,
    toolbar: d,
    placeholder: f,
    htmlSupport: g,
    wordCount: b,
    typo3link: C,
    removePlugins: P,
    ...u
  } = o;
  "extraPlugins" in u && delete u.extraPlugins, "contentsCss" in u && delete u.contentsCss;
  let x = {};
  "fullscreen" in u && (x = u.fullscreen, delete u.fullscreen), x.container = document.querySelector(".module-body");
  const w = await N(I, s, p),
    m = {licenseKey: "GPL", ...u, toolbar: d, plugins: w, placeholder: f, wordCount: b, typo3link: C || null, removePlugins: P || [], fullscreen: x};
  g !== void 0 && (m.htmlSupport = E(g)), m?.typing?.transformations !== void 0 && (m.typing.transformations = E(m.typing.transformations));
  const i = await n.create(e, m);
  if (j(i, l, a), A(r, i, b), H(i, t), i.model.document.on("change:data", () => {
    i.updateSourceElement(), e.dispatchEvent(new Event("change", {bubbles: !0, cancelable: !0}));
  }), i.plugins.has("SourceEditing")) {
    const k = i.plugins.get("SourceEditing");
    k.on("change:isSourceEditingMode", (K, L, S) => {
      for (const [T] of i.editing.view.domRoots) if (S) {
        const y = i.ui.getEditableElement(`sourceEditing:${T}`);
        if (y instanceof HTMLTextAreaElement) new M("input", () => {
          k.updateEditorData();
        }, 100).bindTo(y); else throw new Error("Cannot find textarea related to source editing. Has CKEditor been upgraded?");
      }
    });
  }
  return c && import("@ckeditor/ckeditor5-inspector").then(({default: k}) => k.attach(i, {isCollapsed: !0})), i;
}

async function N(o, e, r) {
  const n = v(r || []), s = v([...o, ...e || []]).map(t => {
    const {module: c} = t;
    let {exports: d} = t;
    for (const f of n) f.module === c && (d = d.filter(g => !f.exports.includes(g)));
    return {module: c, exports: d};
  }), p = await Promise.all(s.map(async t => {
    try {
      return {module: await import(t.module), exports: t.exports};
    } catch (c) {
      return console.error(`Failed to load CKEditor 5 module ${t.module}`, c), {module: null, exports: []};
    }
  })), l = [];
  p.forEach(({module: t, exports: c}) => {
    for (const d of c) d in t ? l.push(t[d]) : console.error(`CKEditor 5 plugin export "${d}" not available in`, t);
  });
  const a = l.filter(t => t.overrides?.length > 0).map(t => t.overrides).flat(1);
  return l.filter(t => !a.includes(t));
}

function j(o, e, r) {
  const n = o.editing.view, s = {"min-height": r, "min-width": e};
  Object.keys(s).forEach(p => {
    const l = s[p];
    if (!l) return;
    let a;
    typeof l == "number" || !Number.isNaN(Number(a)) ? a = `${l}px` : a = l, n.change(t => {
      t.setStyle(p, a, n.document.getRoot());
    });
  });
}

function A(o, e, r) {
  if (e.plugins.has("WordCount") && (r?.displayWords || r?.displayCharacters)) {
    const n = e.plugins.get("WordCount");
    o.appendChild(n.wordCountContainer);
  }
}

function H(o, e) {
  e && o.enableReadOnlyMode("typo3-lock");
}

function h(o, e) {
  if (typeof o == "object") {
    if (Array.isArray(o)) return o.map(n => e(n) ?? h(n, e));
    const r = {};
    for (const [n, s] of Object.entries(o)) r[n] = e(s) ?? h(s, e);
    return r;
  }
  return o;
}

function E(o) {
  return h(o, e => {
    if (typeof e == "object" && "pattern" in e && typeof e.pattern == "string") {
      const r = e;
      return new RegExp(r.pattern, r.flags || void 0);
    }
    return null;
  });
}

function v(o) {
  return o.map(e => typeof e == "string" ? {module: e, exports: ["default"]} : e);
}

export {O as initCKEditorInstance};
