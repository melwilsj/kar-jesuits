import { api, executeWithRetry } from "@/services/api";
import { Role, Institution } from "./institution";

// Re-export the Institution type
export { Institution, Role };

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

export interface Jesuit {
  id: number;
  user_id: number;
  name: string;
  code: string;
  category: 'P' | 'Bp' | 'NS' | 'F' | 'S';
  photo_url: string | null;
  phone_number: string | null;
  email: string | null;
  dob: string;
  joining_date: string;
  priesthood_date: string | null;
  final_vows_date: string | null;
  academic_qualifications: string | null;
  is_external: boolean;
  notes: string | null;
  province_only: boolean;
  province_id: number | null;
  region_id: number | null;
  current_community_id: number | null;
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

export interface InstitutionsResponse {
  success: boolean;
  message: string;
  data: Institution[];
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

export interface Commission {
  id: number;
  name: string;
  type: 'education' | 'social' | 'formation' | 'pastoral' | 'other';
  coordinator?: string;
  description?: string;
  members?: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
}

export interface StatisticData {
  label: string;
  value: number | string;
  percentage?: number;
  color?: string;
}

// Update pagination structure to match new API response format
export interface PaginatedData<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: {
    url: string | null;
    label: string;
    active: boolean;
  }[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

// Update JesuitsResponse to support pagination
export interface PaginatedJesuitsResponse {
  success: boolean;
  message: string;
  data: PaginatedData<Jesuit>;
  meta: any | null;
}

// Add Event type
export interface Event {
  id: number;
  title: string;
  description: string;
  type: string;
  event_type: string;
  start_datetime: string;
  end_datetime: string;
  venue: string | null;
  province: string;
  region: string | null;
  community: string;
  attachments: any[];
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
  
  // Update Jesuit filters with pagination support
  fetchJesuitsInFormation: (page = 1) => executeWithRetry(() => 
    api.get<PaginatedJesuitsResponse>(`/province/jesuits/formation?page=${page}`)
  ),
  fetchJesuitsInCommonHouses: (page = 1) => executeWithRetry(() => 
    api.get<PaginatedJesuitsResponse>(`/province/jesuits/common-houses?page=${page}`)
  ),
  fetchJesuitsInOtherProvinces: (page = 1) => executeWithRetry(() => 
    api.get<PaginatedJesuitsResponse>(`/province/jesuits/other-provinces?page=${page}`)
  ),
  fetchJesuitsOutsideIndia: (page = 1) => executeWithRetry(() => 
    api.get<PaginatedJesuitsResponse>(`/province/jesuits/outside-india?page=${page}`)
  ),
  fetchOtherProvinceJesuitsResiding: (page = 1) => executeWithRetry(() => 
    api.get<PaginatedJesuitsResponse>(`/province/jesuits/other-residing?page=${page}`)
  ),
  
  // Institution filters
  fetchInstitutions: () => executeWithRetry(() => 
    api.get<ApiResponse<Institution[]>>('/province/institutions')
  ),
  fetchEducationalInstitutions: () => executeWithRetry(() => 
    api.get<ApiResponse<Institution[]>>('/province/institutions/educational')
  ),
  fetchSocialCenters: () => executeWithRetry(() => 
    api.get<ApiResponse<Institution[]>>('/province/institutions/social-centers')
  ),
  fetchParishes: () => executeWithRetry(() => 
    api.get<ApiResponse<Institution[]>>('/province/institutions/parishes')
  ),
  
  // Commission filters
  fetchAllCommissions: () => executeWithRetry(() => 
    api.get<ApiResponse<Commission[]>>('/province/commissions')
  ),
  fetchCommissionsByType: (type: string) => executeWithRetry(() => 
    api.get<ApiResponse<Commission[]>>(`/province/commissions/${type}`)
  ),
  
  // Statistics
  fetchAgeDistributionStats: () => executeWithRetry(() => 
    api.get<ApiResponse<StatisticData[]>>('/province/statistics/age-distribution')
  ),
  fetchFormationStats: () => executeWithRetry(() => 
    api.get<ApiResponse<StatisticData[]>>('/province/statistics/formation')
  ),
  fetchGeographicalStats: () => executeWithRetry(() => 
    api.get<ApiResponse<StatisticData[]>>('/province/statistics/geographical')
  ),
  fetchMinistryStats: () => executeWithRetry(() => 
    api.get<ApiResponse<StatisticData[]>>('/province/statistics/ministry')
  ),
  fetchYearlyTrendsStats: () => executeWithRetry(() => 
    api.get<ApiResponse<StatisticData[]>>('/province/statistics/yearly-trends')
  ),
};
