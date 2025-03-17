import { api, executeWithRetry } from "@/services/api";

export interface ApiResponse<T> {
  data: T;
  message?: string;
  meta?: {
    pagination?: {
      total: number;
      current_page: number;
      per_page: number;
    };
  };
}

interface Province {
  id: number;
  name: string;
  code: string;
}

interface Region {
  id: number;
  name: string;
  code: string;
}

export interface Community {
  id: number;
  name: string;
  code: string;
  province_id: number;
  region_id: number | null;
  assistancy_id: number | null;
  parent_community_id: number | null;
  superior_type: string;
  address: string;
  diocese: string;
  taluk: string;
  district: string;
  state: string;
  phone: string | null;
  email: string | null;
  is_formation_house: boolean;
  is_common_house: boolean;
  is_attached_house: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  institutions: any[];
  province: {
    id: number;
    name: string;
    code: string;
  };
  region: null | {
    id: number;
    name: string;
    code: string;
  };
  assistancy: null | {
    id: number;
    name: string;
    code: string;
  };
  province_only: boolean;
  region_member: boolean;
  common_house: boolean;
  superior: null | any;
}

interface Role {
  type: string;
  institution: string;
}

export interface Jesuit {
  id: number;
  name: string;
  code: string;
  category: 'P' | 'Bp' | 'NS' | 'F' | 'S';
  photo_url: string | null;
  phone_number: string | null;
  email: string | null;
  dob: string;
  joining_date: string;
  priesthood_date: string | null;
  province_only: boolean;
  region_id: number | null;
  current_community: string | null;
  province: string | null;
  region: string | null;
  roles: Role[];
}

export interface ProvinceJesuitsResponse {
  success: boolean;
  message: string;
  data: {
    province: Province;
    regions: Region[];
    members: Jesuit[];
  }
}

export interface CommunitiesResponse {
  success: boolean;
  message: string;
  data: Community[];
}

export interface CurrentJesuit {
  id: number;
  name: string;
  code: string;
  category: string;
  photo_url: string | null;
  email: string;
  phone_number: string | null;
  dob: string | null;
  joining_date: string | null;
  priesthood_date: string | null;
  final_vows_date: string | null;
  academic_qualifications: {
    degree: string;
    institution: string;
    year: number;
    specialization?: string;
  }[];
  publications: {
    title: string;
    year: number;
    type: string;
    details?: string;
  }[];
  current_community: string;
  province: string;
  region: string | null;
  roles: {
    type: string;
    institution: string | null;
  }[];
  formation: {
    stage: string;
    start_date: string;
    end_date: string | null;
    status: string;
  }[];
  documents: {
    id: number;
    name: string;
    type: string;
    url: string;
  }[];
}

// Use in API calls
export const dataAPI = {
  fetchJesuits: () => executeWithRetry(() => 
    api.get<ApiResponse<ProvinceJesuitsResponse>>('/province-jesuits')
  ),
  fetchCommunities: () => executeWithRetry(() => 
    api.get<ApiResponse<CommunitiesResponse>>('/province-communities')
  ),
  fetchCurrentJesuit: () => executeWithRetry(() => 
    api.get<ApiResponse<CurrentJesuit>>('/current-jesuit')
  ),
};
