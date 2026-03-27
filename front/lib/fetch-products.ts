import type { AxiosResponse } from "axios";
import api from "@/lib/axios";

/** Minimal shape used by callers; full payload is defined by the API. */
export type ProductsApiData = { categories?: unknown[] };

let inFlight: Promise<AxiosResponse<ProductsApiData>> | null = null;

/**
 * Single-flight fetch for GET /products. Concurrent callers share one HTTP request
 * until it settles (success or failure), then the next call may start a new request.
 */
export function fetchProducts(): Promise<AxiosResponse<ProductsApiData>> {
  if (!inFlight) {
    inFlight = api.get("/products").finally(() => {
      inFlight = null;
    });
  }
  return inFlight;
}
