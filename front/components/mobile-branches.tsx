"use client";

import { useState, useEffect } from "react";
import { Banner } from "@/components/banner";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChartSimple, faHeart, faShop, faUser, faWallet } from "@fortawesome/free-solid-svg-icons";
import { PlusCircle, Home, ChevronRight, MapPin } from "lucide-react";
import { startLoading, stopLoading } from "@/lib/loading";
import { MobileNewBranch } from "./mobile-new-branch";
import { MobileEditBranch } from "./mobile-edit-branch";
import { MobilePageHeader } from "@/components/mobile-page-header";
import api from "@/lib/axios";

interface Branch {
  id: number;
  name: string;
  contact_name?: string | null;
  address_line1: string;
  address_line2: string;
  city: string;
  zip_code: string;
  country: string;
  state: string;
  is_default: boolean;
}

interface MobileBranchesProps {
  onNavigate: (page: any, favorites?: boolean) => void;
  onBack: () => void;
}

function formatBranchAddress(branch: Branch) {
  const parts = [branch.address_line1, branch.address_line2, branch.city, branch.zip_code].filter(Boolean);
  return parts.length ? parts.join(", ") : "Address line 1, address line 2, city, Postcode";
}

export function MobileBranches({ onNavigate, onBack }: MobileBranchesProps) {
  const [branches, setBranches] = useState<Branch[]>([]);
  const [showNewBranch, setShowNewBranch] = useState(false);
  const [showEditBranch, setShowEditBranch] = useState(false);
  const [selectedBranch, setSelectedBranch] = useState<Branch | null>(null);

  const fetchBranches = async () => {
    try {
      startLoading();
      const response = await api.get("/branches");
      if (response.data.success) {
        setBranches(response.data.branches);
      }
    } catch (error) {
      console.error("Error fetching branches:", error);
      alert("Failed to load branches");
    } finally {
      stopLoading();
    }
  };

  useEffect(() => {
    fetchBranches();
  }, []);

  const handleBranchClick = async (branchId: number) => {
    try {
      startLoading();
      const response = await api.get(`/branches/${branchId}`);
      if (response.data.success) {
        setSelectedBranch(response.data.branch);
        setShowEditBranch(true);
      }
    } catch (error) {
      console.error("Error fetching branch:", error);
      alert("Failed to load branch details");
    } finally {
      stopLoading();
    }
  };

  if (showNewBranch) {
    return (
      <MobileNewBranch onNavigate={onNavigate} onBack={() => setShowNewBranch(false)} onBranchSaved={fetchBranches} />
    );
  }

  if (showEditBranch && selectedBranch) {
    return (
      <MobileEditBranch
        key={selectedBranch.id}
        branchDetails={selectedBranch}
        onNavigate={onNavigate}
        onBack={() => setShowEditBranch(false)}
        onBranchUpdated={fetchBranches}
      />
    );
  }

  return (
    <div
      className="relative mx-auto flex h-[100dvh] min-h-0 w-full max-w-[402px] flex-col bg-[#FAFBFD]"
      style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
    >
      <MobilePageHeader title="My Branches" onBack={onBack} />

      <main className="scrollbar-hide min-h-0 flex-1 overflow-x-hidden overflow-y-auto bg-[#FAFBFD] px-6 pb-[88px] pt-4">
        <div className="mx-auto flex w-full max-w-[354px] flex-col gap-4">
          <Banner className="h-[89px] max-w-[354px] rounded-[10px] border border-[#E2E2E2]" />

          <button
            type="button"
            onClick={() => setShowNewBranch(true)}
            className="flex h-[50px] w-full shrink-0 items-center justify-center gap-4 rounded-[25px] bg-[#4A90E5] px-8 text-[17px] font-bold text-white shadow-sm transition-opacity hover:opacity-95 active:opacity-90"
          >
            <PlusCircle className="h-6 w-6 shrink-0 text-white" strokeWidth={2} aria-hidden />
            <span>Add Branch</span>
          </button>

          <div className="flex flex-col gap-4">
            {branches.length > 0 ? (
              branches.map((branch) => (
                <button
                  key={branch.id}
                  type="button"
                  onClick={() => handleBranchClick(branch.id)}
                  className="flex w-full cursor-pointer items-center gap-4 rounded-[5px] border border-[#4A90E5] bg-white py-2 pl-4 pr-4 text-left transition-opacity hover:opacity-95 active:opacity-90"
                >
                  <div className="flex min-w-0 flex-1 items-center gap-4">
                    <div
                      className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#EAF2FC]"
                      aria-hidden
                    >
                      <Home className="h-5 w-5 text-[#4A90E5]" strokeWidth={2.25} />
                    </div>
                    <div className="min-w-0 flex-1 border-l border-[#E2E2E2] pl-4">
                      <p className="text-[14px] font-bold leading-tight text-[#3D495E]">{branch.name || "Company Name"}</p>
                      {branch.contact_name ? (
                        <p className="mt-0.5 text-[12px] font-medium leading-snug text-[#5C6B8A]">Contact: {branch.contact_name}</p>
                      ) : null}
                      <p className="mt-1.5 text-[12px] font-normal leading-snug text-[#8F98AD]">{formatBranchAddress(branch)}</p>
                    </div>
                  </div>
                  <ChevronRight className="h-5 w-5 shrink-0 text-[#4A90E5]" strokeWidth={2.5} aria-hidden />
                </button>
              ))
            ) : (
              <div className="flex flex-col items-center py-10 text-[#8F98AD]">
                <MapPin className="mb-3 h-12 w-12 text-[#CBD5E1]" strokeWidth={1.75} aria-hidden />
                <h3 className="text-[16px] font-bold text-[#3D495E]">No branches yet</h3>
                <p className="mt-2 text-center text-[14px] text-[#8F98AD]">Add your first branch to get started</p>
              </div>
            )}
          </div>
        </div>
      </main>

      <nav
        className="fixed bottom-0 left-1/2 z-50 box-border flex h-[64px] w-full max-w-[402px] -translate-x-1/2 flex-col rounded-t-[10px] bg-white px-[43px] pb-4 pt-2 shadow-[0_-5px_15px_0_rgba(85,94,88,0.09)]"
        aria-label="Main navigation"
        style={{ fontFamily: "Roboto, system-ui, sans-serif" }}
      >
        <div className="flex min-h-0 w-full flex-1 items-center justify-center">
          <div className="flex h-[40px] w-[316px] max-w-full items-center justify-center gap-2">
            <button
              type="button"
              onClick={() => onNavigate("dashboard")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faChartSimple} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Dashboard</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", false)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faShop} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Shop</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("shop", true)}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faHeart} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Favourites</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("wallet")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-1 opacity-60 text-[#BDC7DE]"
            >
              <FontAwesomeIcon icon={faWallet} className="h-[20px] w-[20px] shrink-0 text-[20px] leading-none text-[#BDC7DE]" aria-hidden />
              <span className="text-center text-[10px] font-bold leading-none">Wallet</span>
            </button>
            <button
              type="button"
              onClick={() => onNavigate("account")}
              className="flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-0.5 text-[#4A90E5]"
              aria-current="page"
            >
              <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#4A90E5]">
                <FontAwesomeIcon icon={faUser} className="h-[18px] w-[18px] text-[18px] leading-none text-white" aria-hidden />
              </span>
              <span className="text-center text-[11px] font-bold leading-none text-[#4A90E5]">Account</span>
              <span className="h-0.5 w-6 shrink-0 rounded-full bg-[#4A90E5]" aria-hidden />
            </button>
          </div>
        </div>
      </nav>
    </div>
  );
}
