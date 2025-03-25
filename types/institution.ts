export interface Institution {
    id: number;
    name: string;
    code: string;
    community_id: number;
    type: InstitutionType;
    description: string | null;
    contact_details: ContactDetails;
    student_demographics?: StudentDemographics;
    staff_demographics?: StaffDemographics;
    beneficiaries?: any[];
    address: string;
    diocese: string | null;
    taluk: string | null;
    district: string | null;
    state: string | null;
    community: CommunityInfo;
    directors: Director[];
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
  }
  
  export type InstitutionType = 
    | 'school' 
    | 'college' 
    | 'university' 
    | 'hostel' 
    | 'community_college' 
    | 'iti' 
    | 'parish' 
    | 'social_center'
    | 'retreat_center'
    | 'farm' 
    | 'ngo' 
    | 'other';
  
  export interface ContactDetails {
    phones: string[];
    emails: string[];
    website?: string;
  }
  
  export interface StudentDemographics {
    catholics: number;
    other_christians: number;
    non_christians: number;
    boys: number;
    girls: number;
  }
  
  export interface StaffDemographics {
    jesuits: number;
    other_religious: number;
    catholics: number;
    others: number;
    total: number;
  }
  
  export interface CommunityInfo {
    id: number;
    name: string;
    code: string;
  }
  
  export interface Director {
    id: number;
    jesuit_id: number;
    role_type_id: number;
    assignable_type: string;
    assignable_id: number;
    start_date: string;
    end_date: string | null;
    is_active: boolean;
    notes: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    jesuit: {
        id: number;
        user_id: number;
        user: {
            id: number;
            name: string;
        };
    };
    roles?: Role[];
  }
  
  export interface Role {
    type: string;
    institution: string | null;
  }