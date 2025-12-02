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
  onNavigate: (page: any) => void;
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
    <div className="w-full max-w-[1000px] mx-auto bg-white">
      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
        <div className="flex items-center">
          <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
            <FontAwesomeIcon icon={faStore} className="text-green-600" style={{ width: "27px", height: "24px" }} />
          </div>
          <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">Account</span>
          &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
          &nbsp;<span className="text-[16px] font-semibold">My Branches</span>
        </div>
      </div>

      <Banner />

      <div className="p-[10px] mb-[82px]">
        <Button
          onClick={() => setShowNewBranch(true)}
          className="w-full h-[45px] cursor-pointer rounded bg-green-600 text-white font-semibold flex items-center justify-center"
        >
          <FontAwesomeIcon icon={faPlus} style={{ width: "14px", height: "16px" }} />
          <span className="text-[16px] leading-[15px]">New Branch</span>
        </Button>

        <hr className="my-[20px]" />

        {branches.length > 0 ? (
          branches.map((branch) => (
            <div
              key={branch.id}
              onClick={() => handleBranchClick(branch.id)}
              className="min-h-[42px] leading-[16px] border border-green-600 mb-[10px] p-[12px] rounded-md cursor-pointer flex items-center justify-between"
            >
              <div className="flex items-center">
                <FontAwesomeIcon
                  icon={faStore}
                  className="text-green-600"
                  style={{ width: "18px", height: "16px" }}
                />
                <div className="mx-[8px]">
                  <strong className="text-[14px] font-semibold">{branch.name ? ` ${branch.name},` : ""}</strong>&nbsp;
                  <strong className="text-[14px] font-semibold">
                    {branch.address_line1}
                    {branch.address_line2 ? `, ${branch.address_line2}` : ""}
                    {branch.city ? `, ${branch.city}` : ""}
                    {branch.state ? `, ${branch.state}` : ""}
                    {branch.country ? `, ${branch.country}` : ""}
                    {branch.zip_code ? `, ${branch.zip_code.replace(/^(\w{3})(\w{3})$/, '$1 $2')}` : ""}
                  </strong>
                </div>
              </div>
              <FontAwesomeIcon
                icon={faChevronRight}
                className="text-green-600"
                style={{ width: "12.5px", height: "21px" }}
              />
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
      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faShop} className="text-[#607565]" style={{ width: "30px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faWallet} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faUser} className="text-[#607565]" style={{ width: "21px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
