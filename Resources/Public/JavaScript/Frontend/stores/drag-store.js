/**
 * A generic store for managing state with change events
 * @template T
 */
class Store extends EventTarget {
  /** @type {T} */
  #data = null;

  /**
   * @param {T} initialValue
   */
  constructor(initialValue) {
    super();
    this.#data = initialValue;
  }

  /**
   * @returns {T}
   */
  get value() {
    return this.#data;
  }

  /**
   * @param {T} value
   */
  set value(value) {
    this.#data = value;
    this.dispatchEvent(new Event('change'));
  }
}

/** @type {Store<false|{table:string,uid:number}>} */
export const dragInProgressStore = new Store(false);
