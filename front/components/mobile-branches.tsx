"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Banner } from "@/components/banner";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { startLoading, stopLoading } from "@/lib/loading";
import {
  faGauge,
  faShop,
  faUser,
  faWallet,
  faMapMarkerAlt,
  faStore,
  faPlus,
  faChevronRight,
  faChevronLeft,
  faHome,
  faChartSimple,
  faHeart,
} from "@fortawesome/free-solid-svg-icons";
import { MobileNewBranch } from "./mobile-new-branch";
import { MobileEditBranch } from "./mobile-edit-branch";
import api from "@/lib/axios"; // your Axios instance

interface Branch {
  id: number;
  name: string;
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

export function MobileBranches({ onNavigate, onBack }: MobileBranchesProps) {
  const [branches, setBranches] = useState<Branch[]>([]);
  const [showNewBranch, setShowNewBranch] = useState(false);
  const [showEditBranch, setShowEditBranch] = useState(false);
  const [selectedBranch, setSelectedBranch] = useState<Branch | null>(null);

  // ✅ Fetch branches on component mount
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
      <MobileNewBranch 
        onNavigate={onNavigate} 
        onBack={() => setShowNewBranch(false)}
        onBranchSaved={fetchBranches}
      />
    );
  }

  if (showEditBranch && selectedBranch) {
    return (
      <MobileEditBranch
        branchDetails={selectedBranch}
        onNavigate={onNavigate}
        onBack={() => setShowEditBranch(false)}
        onBranchUpdated={fetchBranches}
      />
    );
  }

  return (
    <div className="w-full max-w-[402px] mx-auto bg-[#F8F7FC] min-h-screen flex flex-col">
      {/* Header */}
      <div className="bg-[#F8F7FC] flex items-center justify-between px-4 h-[56px] sticky top-0 z-50">
        <button onClick={onBack} className="flex items-center gap-1 text-[#8F98AD] font-bold text-[13px]">
          <FontAwesomeIcon icon={faChevronLeft} className="text-[14px]" />
          <span>Back</span>
        </button>
        <h1 className="text-[16px] font-bold text-[#3D495E]">My Branches</h1>
        <div className="w-[40px]"></div> {/* Spacer for centering */}
      </div>

      <div className="flex w-full justify-center px-3 py-3">
        <Banner />
      </div>

      <div className="px-4 py-4 flex-1">
        <Button
          onClick={() => setShowNewBranch(true)}
          className="w-full h-[52px] cursor-pointer rounded-[26px] bg-[#4A90E5] text-white font-bold flex items-center justify-center gap-2 hover:bg-[#3B7DCF] transition-colors shadow-sm"
        >
          <div className="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center">
            <FontAwesomeIcon icon={faPlus} className="text-[14px]" />
          </div>
          <span className="text-[17px]">Add Branch</span>
        </Button>

        <div className="mt-8 space-y-4">

        {branches.length > 0 ? (
          branches.map((branch) => (
            <div
              key={branch.id}
              onClick={() => handleBranchClick(branch.id)}
              className="bg-white border border-[#DCE1EE] p-4 rounded-[10px] cursor-pointer flex items-center justify-between shadow-sm hover:border-[#4A90E5] transition-all"
            >
              <div className="flex items-center gap-4">
                <div className="w-[42px] h-[42px] bg-[#EAF0FA] rounded-full flex items-center justify-center">
                  <FontAwesomeIcon
                    icon={faHome}
                    className="text-[#4A90E5] text-[20px]"
                  />
                </div>
                <div className="flex flex-col">
                  <span className="text-[14px] font-bold text-[#3D495E]">{branch.name || "Company Name"}</span>
                  <span className="text-[12px] text-[#8F98AD] font-medium leading-tight">
                    {branch.address_line1}
                    {branch.address_line2 ? `, ${branch.address_line2}` : ""}
                    {branch.city ? `, ${branch.city}` : ""}
                    {branch.zip_code ? `, ${branch.zip_code}` : ""}
                  </span>
                </div>
              </div>
              <div className="flex items-center">
                <div className="h-[32px] w-[1px] bg-[#F1F2F7] mx-2"></div>
                <FontAwesomeIcon
                  icon={faChevronRight}
                  className="text-[#4A90E5] text-[18px] ml-2"
                />
              </div>
            </div>
          ))
        ) : (
          <div className="text-center py-[40px] text-gray-500">
            <FontAwesomeIcon icon={faMapMarkerAlt} style={{ width: "48px", height: "48px" }} />
            <h3 className="text-[16px] font-medium mb-[8px]">No branches yet</h3>
            <p className="text-[14px]">Add your first branch to get started</p>
          </div>
        )}
      </div>
    </div>
      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[402px] z-50 shadow-[0px_-1px_8px_0px_#555E5814] bg-white">
        <div className="h-[74px] px-2 pt-[8px] pb-[10px] grid grid-cols-5 items-center bg-[#F1F2F7] border-t border-[#E4E7F0]">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faChartSimple} className="text-[23px]" />
            <span>Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faShop} className="text-[23px]" />
            <span>Shop</span>
          </button>
          <button onClick={() => onNavigate("shop", true)} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faHeart} className="text-[23px]" />
            <span>Favourites</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center gap-[4px] text-[#BDC7DE] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faWallet} className="text-[23px]" />
            <span>Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center gap-[4px] text-[#4A90E5] text-[11px] font-bold leading-none">
            <FontAwesomeIcon icon={faUser} className="text-[23px]" />
            <span>Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
