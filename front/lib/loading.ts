export const startLoading = () => 
  window.dispatchEvent(new CustomEvent("global-loading", { detail: { type: "global-loading-start" } }));

export const stopLoading = () => 
  window.dispatchEvent(new CustomEvent("global-loading", { detail: { type: "global-loading-stop" } }));
