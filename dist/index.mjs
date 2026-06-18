function b(e, n = 2) {
  return String(Math.abs(e)).padStart(n, "0");
}
function z(e) {
  return e % 4 === 0 && e % 100 !== 0 || e % 400 === 0;
}
function Y(e, n) {
  return [31, z(e) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][n - 1] ?? 31;
}
function p(e) {
  let n = e.month, s = e.year;
  n < 3 && (n += 12, s -= 1);
  const t = s % 100, i = Math.floor(s / 100);
  return ((e.day + Math.floor(13 * (n + 1) / 5) + t + Math.floor(t / 4) + Math.floor(i / 4) + 5 * i) % 7 + 6) % 7;
}
function Q(e) {
  const n = p(e);
  return n === 0 ? 7 : n;
}
function X(e) {
  const n = p(e);
  return n === 0 || n === 6;
}
function A(e, n) {
  return e.year === n.year && e.month === n.month && e.day === n.day;
}
function M(e, n) {
  return e.year !== n.year ? e.year - n.year : e.month !== n.month ? e.month - n.month : e.day - n.day;
}
function f(e, n) {
  const s = new Date(Date.UTC(e.year, e.month - 1, e.day + n));
  return { year: s.getUTCFullYear(), month: s.getUTCMonth() + 1, day: s.getUTCDate() };
}
function S(e, n) {
  const s = e.year * 12 + (e.month - 1) + n;
  let t = Math.floor(s / 12), i = s % 12 + 1;
  i < 1 && (i += 12, t -= 1);
  const r = Math.min(e.day, Y(t, i));
  return { year: t, month: i, day: r };
}
function _(e) {
  return `${b(e.year, 4)}-${b(e.month)}-${b(e.day)}`;
}
function Z() {
  const e = /* @__PURE__ */ new Date();
  return { year: e.getFullYear(), month: e.getMonth() + 1, day: e.getDate() };
}
function ee() {
  const e = /* @__PURE__ */ new Date();
  return { hour: e.getHours(), minute: e.getMinutes(), second: e.getSeconds() };
}
function H(e) {
  return e.hour * 3600 + e.minute * 60 + e.second;
}
function m(e, n) {
  return H(e) - H(n);
}
function W(e) {
  const n = e.hour % 12;
  return n === 0 ? 12 : n;
}
function P(e) {
  return e.hour >= 12;
}
function te(e) {
  return `${b(e.hour)}:${b(e.minute)}:${b(e.second)}`;
}
const ne = ["Y", "y", "m", "n", "d", "j", "N", "w", "D", "l", "M", "F"], ie = ["H", "G", "h", "g", "i", "s", "A", "a"], G = new Set(ne), se = new Set(ie);
function re(e) {
  return G.has(e);
}
function oe(e) {
  return G.has(e) || se.has(e);
}
function R(e) {
  const n = Array.from(e), s = [];
  for (let t = 0; t < n.length; t++) {
    const i = n[t];
    if (i === "\\") {
      const r = n[t + 1];
      r !== void 0 && (s.push({ type: "literal", value: r }), t++);
      continue;
    }
    s.push({ type: oe(i) ? "token" : "literal", value: i });
  }
  return s;
}
function D(e, n, s, t) {
  let i = "";
  const r = [];
  for (const l of R(n)) {
    if (l.type === "literal") {
      i += F(l.value);
      continue;
    }
    i += ae(l.value, t), r.push(l.value);
  }
  let o;
  try {
    o = new RegExp(`^${i}$`, "iu");
  } catch {
    return { ok: !1, error: "invalid_format" };
  }
  const a = o.exec(e.trim());
  if (a === null)
    return { ok: !1, error: "no_match" };
  const u = {};
  return r.forEach((l, w) => {
    u[l] = a[w + 1] ?? "";
  }), ue(u, s, t);
}
function F(e) {
  return e.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}
function ae(e, n) {
  switch (e) {
    case "Y":
      return "(\\d{4})";
    case "y":
    case "m":
    case "d":
    case "H":
    case "h":
    case "i":
    case "s":
      return "(\\d{2})";
    case "n":
    case "j":
    case "G":
    case "g":
      return "(\\d{1,2})";
    case "N":
      return "([1-7])";
    case "w":
      return "([0-6])";
    case "A":
    case "a":
      return "([AaPp][Mm])";
    case "D":
      return k(n.weekdaysShort);
    case "l":
      return k(n.weekdays);
    case "M":
      return k(n.monthsShort);
    case "F":
      return k(n.months);
    default:
      return F(e);
  }
}
function k(e) {
  return `(${e.map(F).join("|")})`;
}
function ue(e, n, s) {
  let t = null, i = null;
  if (n !== "time") {
    const r = le(e, s, n === "month");
    if (r === null)
      return { ok: !1, error: "invalid_date" };
    t = r;
  }
  if (n === "time" || n === "datetime") {
    const r = ce(e);
    if (r === null)
      return { ok: !1, error: "invalid_time" };
    i = r;
  }
  return { ok: !0, value: { date: t, time: i } };
}
function le(e, n, s = !1) {
  const t = he(e), i = de(e, n), r = fe(e) ?? (s ? 1 : null);
  return t === null || i === null || r === null || i < 1 || i > 12 || r < 1 || r > Y(t, i) ? null : { year: t, month: i, day: r };
}
function ce(e) {
  const n = me(e), s = e.i ? h(e.i) : null, t = e.s ? h(e.s) : 0;
  return n === null || s === null || n < 0 || n > 23 || s < 0 || s > 59 || t < 0 || t > 59 ? null : { hour: n, minute: s, second: t };
}
function he(e) {
  return e.Y ? h(e.Y) : e.y ? 2e3 + h(e.y) : null;
}
function de(e, n) {
  if (e.m) return h(e.m);
  if (e.n) return h(e.n);
  for (const s of ["F", "M"]) {
    const t = e[s];
    if (t) {
      const i = pe(t, n);
      if (i !== null) return i;
    }
  }
  return null;
}
function fe(e) {
  return e.d ? h(e.d) : e.j ? h(e.j) : null;
}
function me(e) {
  if (e.H) return h(e.H);
  if (e.G) return h(e.G);
  const n = e.h || e.g;
  if (n) {
    const s = h(n) % 12, t = ye(e);
    return t === "PM" ? s + 12 : t === "AM" ? s : h(n);
  }
  return null;
}
function ye(e) {
  const n = e.A || e.a;
  return n ? n[0]?.toUpperCase() === "P" ? "PM" : "AM" : null;
}
function pe(e, n) {
  const s = e.trim().toLowerCase(), t = n.months.findIndex((r) => r.toLowerCase() === s);
  if (t !== -1) return t + 1;
  const i = n.monthsShort.findIndex((r) => r.toLowerCase() === s);
  return i !== -1 ? i + 1 : null;
}
function h(e) {
  return parseInt(e, 10);
}
function we(e) {
  return {
    mode: e.mode,
    min: L(e.min, e),
    max: L(e.max, e),
    disabledDates: new Set(e.disabledDates),
    disabledWeekdays: new Set(e.disabledWeekdays),
    disabledTimes: e.disabledTimes.map((n) => ({
      from: j(n.from, e.locale),
      to: j(n.to, e.locale)
    })).filter((n) => n.from !== null && n.to !== null),
    minuteStep: e.minuteStep
  };
}
function L(e, n) {
  if (e === null || e.trim() === "")
    return null;
  const s = D(e, n.valueFormat, n.mode, n.locale);
  return s.ok ? s.value : null;
}
function j(e, n) {
  const s = D(e, "H:i:s", "time", n);
  return s.ok ? s.value.time : null;
}
function K(e, n) {
  const s = e.min?.date ?? null;
  if (s !== null && M(n, s) < 0)
    return !0;
  const t = e.max?.date ?? null;
  return t !== null && M(n, t) > 0 ? !0 : q(e, n);
}
function ge(e, n) {
  if (e.mode === "time") {
    const s = e.min?.time ?? null;
    if (s !== null && m(n, s) < 0)
      return !0;
    const t = e.max?.time ?? null;
    if (t !== null && m(n, t) > 0)
      return !0;
  }
  return e.minuteStep > 1 && n.minute % e.minuteStep !== 0 ? !0 : e.disabledTimes.some(
    (s) => m(n, s.from) >= 0 && m(n, s.to) <= 0
  );
}
function ve(e, n, s) {
  if (e.min?.date && e.min.time) {
    if (I(n, s, e.min.date, e.min.time) < 0)
      return !0;
  } else if (e.min?.date && M(n, e.min.date) < 0)
    return !0;
  if (e.max?.date && e.max.time) {
    if (I(n, s, e.max.date, e.max.time) > 0)
      return !0;
  } else if (e.max?.date && M(n, e.max.date) > 0)
    return !0;
  return q(e, n) || e.minuteStep > 1 && s.minute % e.minuteStep !== 0 ? !0 : e.disabledTimes.some(
    (t) => m(s, t.from) >= 0 && m(s, t.to) <= 0
  );
}
function q(e, n) {
  return e.disabledWeekdays.has(p(n)) || e.disabledDates.has(_(n));
}
function I(e, n, s, t) {
  const i = M(e, s);
  return i !== 0 ? i : m(n, t);
}
const De = 42;
function be(e, n, s, t, i, r, o) {
  const a = (s % 7 + 7) % 7, u = { year: e, month: n, day: 1 }, l = (p(u) - a + 7) % 7, w = f(u, -l), g = [];
  let d = [];
  for (let v = 0; v < De; v++) {
    const c = f(w, v);
    d.push({
      date: _(c),
      day: c.day,
      month: c.month,
      year: c.year,
      weekday: p(c),
      isToday: A(c, t),
      isOutsideMonth: c.month !== n,
      isDisabled: K(o, c),
      isSelected: i !== null && A(c, i),
      isWeekend: X(c),
      isFocused: r !== null && A(c, r)
    }), d.length === 7 && (g.push(d), d = []);
  }
  return g;
}
function $(e, n) {
  return e * 12 + (n - 1);
}
function B(e, n, s) {
  const t = $(n, s), i = e.min?.date ?? null;
  if (i !== null && t < $(i.year, i.month))
    return !0;
  const r = e.max?.date ?? null;
  return r !== null && t > $(r.year, r.month);
}
function Me(e, n, s, t, i, r) {
  const o = [];
  for (let a = 1; a <= 12; a++)
    o.push({
      year: e,
      month: a,
      label: n.monthsShort[a - 1] ?? String(a),
      isToday: s.year === e && s.month === a,
      isSelected: t !== null && t.year === e && t.month === a,
      isDisabled: B(r, e, a),
      isFocused: i === a
    });
  return o;
}
function E(e, n, s) {
  let t = "";
  for (const i of R(n)) {
    if (i.type === "literal") {
      t += i.value;
      continue;
    }
    t += Se(i.value, e.date, e.time, s);
  }
  return t;
}
function y(e, n = 2) {
  return String(e).padStart(n, "0");
}
function Se(e, n, s, t) {
  if (re(e)) {
    if (n === null)
      throw new Error(`Format token "${e}" requires a date but the value has none.`);
    return ke(e, n, t);
  }
  if (s === null)
    throw new Error(`Format token "${e}" requires a time but the value has none.`);
  return Te(e, s, t);
}
function ke(e, n, s) {
  switch (e) {
    case "Y":
      return y(n.year, 4);
    case "y":
      return y(n.year % 100);
    case "m":
      return y(n.month);
    case "n":
      return String(n.month);
    case "d":
      return y(n.day);
    case "j":
      return String(n.day);
    case "N":
      return String(Q(n));
    case "w":
      return String(p(n));
    case "D":
      return s.weekdaysShort[p(n)] ?? "";
    case "l":
      return s.weekdays[p(n)] ?? "";
    case "M":
      return s.monthsShort[n.month - 1] ?? "";
    case "F":
      return s.months[n.month - 1] ?? "";
    default:
      return e;
  }
}
function Te(e, n, s) {
  switch (e) {
    case "H":
      return y(n.hour);
    case "G":
      return String(n.hour);
    case "h":
      return y(W(n));
    case "g":
      return String(W(n));
    case "i":
      return y(n.minute);
    case "s":
      return y(n.second);
    case "A":
      return P(n) ? s.labels.pm_upper ?? "PM" : s.labels.am_upper ?? "AM";
    case "a":
      return P(n) ? s.labels.pm_lower ?? "pm" : s.labels.am_lower ?? "am";
    default:
      return e;
  }
}
const xe = 24 * 60;
function Ae(e, n, s, t) {
  const i = Math.max(1, e.minuteStep), r = e.hourCycle === 12 ? "h:i A" : "H:i", o = [];
  for (let a = 0; a < xe; a += i) {
    const u = { hour: Math.floor(a / 60), minute: a % 60, second: 0 }, l = e.mode === "datetime" && s !== null ? ve(n, s, u) : ge(n, u);
    o.push({
      value: te(u),
      label: E({ date: null, time: u }, r, e.locale),
      isSelected: t !== null && m(u, t) === 0,
      isDisabled: l
    });
  }
  return o;
}
class $e {
  constructor(n) {
    this.wire = n;
  }
  isAvailable() {
    return !0;
  }
  get(n) {
    const s = this.wire.get ?? this.wire.$get;
    return typeof s == "function" ? s.call(this.wire, n) : void 0;
  }
  async set(n, s, t) {
    const i = this.wire.set ?? this.wire.$set;
    typeof i == "function" && await Promise.resolve(i.call(this.wire, n, s, t));
  }
  watch(n, s) {
    if (typeof this.wire.$watch != "function")
      return null;
    const t = this.wire.$watch(n, s);
    return typeof t == "function" ? t : null;
  }
  hasErrorFor(n) {
    const s = this.wire.$errors ?? this.wire.errors;
    if (s === null || typeof s != "object")
      return !1;
    const t = s;
    if (n in t)
      return !0;
    const i = t.errors;
    return i !== null && typeof i == "object" && n in i;
  }
}
class Ee {
  isAvailable() {
    return !1;
  }
  get() {
  }
  async set() {
  }
  watch() {
    return null;
  }
  hasErrorFor() {
    return !1;
  }
}
function N(e) {
  if (e !== null && typeof e == "object") {
    const n = e;
    if (typeof n.set == "function" || typeof n.$set == "function")
      return new $e(n);
  }
  return new Ee();
}
function Ve(e, n, s) {
  e.style.position = "fixed", e.style.maxHeight = "";
  const t = n.getBoundingClientRect(), i = window.innerHeight, r = window.innerWidth, o = 8, a = 8, u = e.offsetHeight, l = e.offsetWidth, w = i - t.bottom, g = t.top;
  let d = s.startsWith("top");
  const v = d ? g : w, c = d ? w : g;
  v < u + o + a && c > v && (d = !d);
  const J = (d ? g : w) - o - a, C = Math.max(0, Math.floor(J));
  e.style.maxHeight = `${C}px`, e.style.overflowY = "auto";
  const O = Math.min(u, C);
  let T = d ? t.top - o - O : t.bottom + o;
  T = Math.max(a, Math.min(T, i - O - a)), e.style.top = `${Math.round(T)}px`, e.style.bottom = "auto";
  let x = s.endsWith("end") ? t.right - l : t.left;
  x = Math.max(a, Math.min(x, r - l - a)), e.style.left = `${Math.round(x)}px`, e.style.right = "auto";
}
const Ye = { hour: 0, minute: 0, second: 0 };
function _e(e) {
  const n = Z();
  return {
    config: e,
    rule: we(e),
    today: n,
    isOpen: !1,
    display: "",
    status: "idle",
    viewYear: n.year,
    viewMonth: n.month,
    focusedDate: n,
    focusedMonth: n.month,
    selectedValue: null,
    committedValue: null,
    generation: 0,
    bridge: N(null),
    unwatch: null,
    get value() {
      return this.valueString();
    },
    get hasValue() {
      return this.selectedValue !== null;
    },
    get weeks() {
      return be(
        this.viewYear,
        this.viewMonth,
        this.config.firstDayOfWeek,
        this.today,
        this.selectedDate(),
        this.focusedDate,
        this.rule
      );
    },
    get monthCells() {
      return Me(
        this.viewYear,
        this.config.locale,
        this.today,
        this.selectedDate(),
        this.focusedMonth,
        this.rule
      );
    },
    get timeOptions() {
      const t = this.selectedValue;
      return Ae(
        this.config,
        this.rule,
        this.selectedDate(),
        t ? t.time : null
      );
    },
    init() {
      if (this.bridge = N(this.$wire ?? null), this.config.value !== null && this.config.value !== "") {
        const i = D(
          this.config.value,
          this.config.valueFormat,
          this.config.mode,
          this.config.locale
        );
        i.ok && (this.selectedValue = i.value, this.committedValue = i.value, this.display = this.formatDisplay(i.value), this.status = "committed");
      }
      const t = this.config.wire.model;
      if (this.bridge.isAvailable() && t !== null) {
        const i = this.bridge.get(t);
        typeof i == "string" && i !== "" && i !== this.valueString() && this.syncFromServer(i), this.unwatch = this.bridge.watch(t, (r) => this.onServerChange(r));
      }
      this.syncViewToSelection(), this.$watch?.("viewMonth", () => this.clampFocus()), this.$watch?.("viewYear", () => this.clampFocus()), this.config.inline && (this.isOpen = !0);
    },
    destroy() {
      this.unwatch !== null && (this.unwatch(), this.unwatch = null);
    },
    open() {
      this.config.disabled || this.config.readonly || this.config.inline || this.isOpen || (this.isOpen = !0, this.syncViewToSelection(), this.$nextTick?.(() => {
        const t = this.$refs?.panel, i = this.$refs?.input ?? this.$el;
        t && i && Ve(t, i, this.config.placement);
      }));
    },
    close(t = !1) {
      this.config.inline || (this.isOpen = !1, this.updateDisplay(), t && this.$refs?.input?.focus());
    },
    toggle() {
      this.isOpen ? this.close() : this.open();
    },
    previousMonth() {
      const t = S({ year: this.viewYear, month: this.viewMonth, day: 1 }, -1);
      this.viewYear = t.year, this.viewMonth = t.month;
    },
    nextMonth() {
      const t = S({ year: this.viewYear, month: this.viewMonth, day: 1 }, 1);
      this.viewYear = t.year, this.viewMonth = t.month;
    },
    previousYear() {
      this.viewYear -= 1;
    },
    nextYear() {
      this.viewYear += 1;
    },
    selectDay(t) {
      t.isDisabled || this.selectDate({ year: t.year, month: t.month, day: t.day });
    },
    selectDate(t) {
      if (K(this.rule, t))
        return;
      if (this.focusedDate = t, this.viewYear = t.year, this.viewMonth = t.month, this.config.mode === "date") {
        this.selectedValue = { date: t, time: null }, this.updateDisplay(), this.commit(), this.close(!0);
        return;
      }
      const i = this.selectedValue?.time ?? Ye;
      this.selectedValue = { date: t, time: i }, this.updateDisplay(), this.commit();
    },
    selectMonth(t, i) {
      B(this.rule, t, i) || (this.viewYear = t, this.focusedMonth = i, this.selectedValue = { date: { year: t, month: i, day: 1 }, time: null }, this.updateDisplay(), this.commit(), this.close(!0));
    },
    selectTime(t) {
      if (t.isDisabled)
        return;
      const i = D(t.value, "H:i:s", "time", this.config.locale);
      if (!i.ok || i.value.time === null)
        return;
      const r = i.value.time;
      if (this.config.mode === "time") {
        this.selectedValue = { date: null, time: r }, this.updateDisplay(), this.commit(), this.close(!0);
        return;
      }
      const o = this.selectedValue?.date ?? this.today;
      this.selectedValue = { date: o, time: r }, this.updateDisplay(), this.commit();
    },
    goToday() {
      if (this.viewYear = this.today.year, this.viewMonth = this.today.month, this.focusedDate = this.today, this.config.mode === "time") {
        const t = ee();
        this.selectedValue = { date: null, time: t }, this.updateDisplay(), this.commit();
        return;
      }
      if (this.config.mode === "month") {
        this.selectMonth(this.today.year, this.today.month);
        return;
      }
      this.selectDate(this.today);
    },
    clear() {
      this.selectedValue = null, this.display = "", this.commit();
    },
    onType() {
      const t = this.display.trim();
      if (t === "") {
        this.selectedValue = null, this.commit();
        return;
      }
      const i = D(t, this.config.displayFormat, this.config.mode, this.config.locale);
      i.ok && (this.selectedValue = i.value, this.syncViewToSelection(), this.commit());
    },
    onInputKeydown(t) {
      if (t.key === "ArrowDown" || t.key === "Enter") {
        t.preventDefault(), this.open(), this.$nextTick?.(() => this.focusActive());
        return;
      }
      t.key === "Escape" && this.isOpen && (t.preventDefault(), this.close(!0));
    },
    onGridKeydown(t) {
      let i = !0;
      switch (t.key) {
        case "ArrowLeft":
          this.focusedDate = f(this.focusedDate, -1);
          break;
        case "ArrowRight":
          this.focusedDate = f(this.focusedDate, 1);
          break;
        case "ArrowUp":
          this.focusedDate = f(this.focusedDate, -7);
          break;
        case "ArrowDown":
          this.focusedDate = f(this.focusedDate, 7);
          break;
        case "Home":
          this.focusedDate = f(this.focusedDate, -((this.focusedDate.day - 1) % 7));
          break;
        case "End":
          this.focusedDate = f(this.focusedDate, 6 - (this.focusedDate.day - 1) % 7);
          break;
        case "PageUp":
          this.focusedDate = S(this.focusedDate, t.shiftKey ? -12 : -1);
          break;
        case "PageDown":
          this.focusedDate = S(this.focusedDate, t.shiftKey ? 12 : 1);
          break;
        case "Enter":
        case " ":
          this.selectDate(this.focusedDate);
          return;
        case "Escape":
          t.preventDefault(), this.close(!0);
          return;
        default:
          i = !1;
      }
      i && (t.preventDefault(), this.viewYear = this.focusedDate.year, this.viewMonth = this.focusedDate.month, this.$nextTick?.(() => this.focusActive()));
    },
    onMonthGridKeydown(t) {
      let i = !0;
      switch (t.key) {
        case "ArrowLeft":
          this.focusedMonth -= 1;
          break;
        case "ArrowRight":
          this.focusedMonth += 1;
          break;
        case "ArrowUp":
          this.focusedMonth -= 3;
          break;
        case "ArrowDown":
          this.focusedMonth += 3;
          break;
        case "PageUp":
          this.viewYear -= 1;
          break;
        case "PageDown":
          this.viewYear += 1;
          break;
        case "Enter":
        case " ":
          this.selectMonth(this.viewYear, this.focusedMonth);
          return;
        case "Escape":
          t.preventDefault(), this.close(!0);
          return;
        default:
          i = !1;
      }
      i && (t.preventDefault(), this.focusedMonth < 1 ? (this.viewYear -= 1, this.focusedMonth += 12) : this.focusedMonth > 12 && (this.viewYear += 1, this.focusedMonth -= 12), this.$nextTick?.(() => this.focusActive()));
    },
    dayClass(t) {
      const i = this.config.classes, r = [i.day ?? ""];
      return t.isOutsideMonth && r.push(i.day_outside ?? ""), t.isWeekend && r.push(i.day_weekend ?? ""), t.isToday && r.push(i.day_today ?? ""), t.isSelected && r.push(i.day_selected ?? ""), t.isDisabled && r.push(i.day_disabled ?? ""), r.filter((o) => o !== "").join(" ");
    },
    monthCellClass(t) {
      const i = this.config.classes, r = [i.month_cell ?? ""];
      return t.isToday && r.push(i.month_cell_today ?? ""), t.isSelected && r.push(i.month_cell_selected ?? ""), t.isDisabled && r.push(i.month_cell_disabled ?? ""), r.filter((o) => o !== "").join(" ");
    },
    timeClass(t) {
      const i = this.config.classes, r = [i.time_option ?? ""];
      return t.isSelected && r.push(i.time_option_selected ?? ""), t.isDisabled && r.push(i.time_option_disabled ?? ""), r.filter((o) => o !== "").join(" ");
    },
    commit() {
      const t = ++this.generation;
      this.status = "pending";
      const i = this.config.wire.model, r = this.valueString();
      if (this.emitChange(), !this.bridge.isAvailable() || i === null) {
        this.committedValue = this.selectedValue, this.status = "committed";
        return;
      }
      const o = this.config.wire.modifiers.includes("live");
      this.bridge.set(i, r, o).catch(() => {
      }).then(() => {
        t === this.generation && this.reconcile(i, r);
      });
    },
    emitChange() {
      const t = this.value, i = this.display, r = this.$refs?.hidden;
      r && (r.dispatchEvent(new Event("input", { bubbles: !0 })), r.dispatchEvent(new Event("change", { bubbles: !0 }))), this.$el?.dispatchEvent(
        new CustomEvent("datepicker:change", {
          detail: { value: t, display: i },
          bubbles: !0
        })
      );
    },
    reconcile(t, i) {
      const r = this.bridge.get(t);
      if (r === void 0) {
        this.committedValue = this.selectedValue, this.status = "committed";
        return;
      }
      const o = r === null ? null : String(r);
      if (o === i) {
        this.bridge.hasErrorFor(t) ? (this.status = "invalid", this.announce("selectedDate")) : (this.committedValue = this.selectedValue, this.status = "committed");
        return;
      }
      this.syncFromServer(o), this.status = "rejected";
    },
    syncFromServer(t) {
      if (t === null || t === "") {
        this.selectedValue = null, this.committedValue = null, this.display = "";
        return;
      }
      const i = D(t, this.config.valueFormat, this.config.mode, this.config.locale);
      i.ok && (this.selectedValue = i.value, this.committedValue = i.value, this.display = this.formatDisplay(i.value));
    },
    onServerChange(t) {
      if (this.status === "pending")
        return;
      const i = t === null ? null : String(t);
      i !== this.valueString() && (this.syncFromServer(i), this.status = "committed");
    },
    valueString() {
      if (this.selectedValue === null)
        return null;
      try {
        return E(this.selectedValue, this.config.valueFormat, this.config.locale);
      } catch {
        return null;
      }
    },
    formatDisplay(t) {
      try {
        return E(t, this.config.displayFormat, this.config.locale);
      } catch {
        return "";
      }
    },
    updateDisplay() {
      this.display = this.selectedValue !== null ? this.formatDisplay(this.selectedValue) : "";
    },
    syncViewToSelection() {
      const t = this.selectedValue?.date ?? this.today;
      this.viewYear = t.year, this.viewMonth = t.month, this.focusedDate = t, this.focusedMonth = t.month;
    },
    clampFocus() {
      if (this.focusedDate.month === this.viewMonth && this.focusedDate.year === this.viewYear)
        return;
      const t = Math.min(this.focusedDate.day, Y(this.viewYear, this.viewMonth));
      this.focusedDate = { year: this.viewYear, month: this.viewMonth, day: t };
    },
    focusActive() {
      const t = this.$refs;
      if (!t)
        return;
      if (this.config.mode === "month") {
        t.monthGrid?.querySelector(
          `[data-month="${this.focusedMonth}"]`
        )?.focus();
        return;
      }
      if (this.config.mode !== "time" && t.grid) {
        const r = _(this.focusedDate);
        t.grid.querySelector(`[data-date="${r}"]`)?.focus();
        return;
      }
      t.timeList?.querySelector("button")?.focus();
    },
    announce(t) {
      const i = this.$refs?.live;
      i && (i.textContent = this.config.locale.labels[t] ?? "");
    },
    selectedDate() {
      return this.selectedValue?.date ?? null;
    }
  };
}
const Ce = {
  code: "en",
  months: [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
  ],
  monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
  weekdays: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
  weekdaysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
  weekdaysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
  firstDayOfWeek: 0,
  labels: {
    today: "Today",
    clear: "Clear",
    close: "Close",
    previousMonth: "Previous month",
    nextMonth: "Next month",
    previousYear: "Previous year",
    nextYear: "Next year",
    chooseDate: "Choose date",
    chooseTime: "Choose time",
    selectedDate: "Selected date",
    monthSelect: "Select month",
    yearSelect: "Select year",
    openCalendar: "Open calendar",
    am_upper: "AM",
    pm_upper: "PM",
    am_lower: "am",
    pm_lower: "pm"
  }
}, Fe = "datepicker";
function V(e) {
  e.data(Fe, (n) => _e(n));
}
function Oe(e) {
  V(e);
}
function U() {
  if (!(typeof window > "u"))
    return window.Alpine;
}
if (typeof document < "u") {
  document.addEventListener("alpine:init", () => {
    const n = U();
    n && V(n);
  });
  const e = U();
  e && V(e);
}
export {
  Fe as DATA_NAME,
  Ce as ENGLISH_LOCALE,
  _e as createDatepickerComponent,
  Oe as default,
  E as format,
  D as parse,
  V as registerDatepicker
};
//# sourceMappingURL=index.mjs.map
