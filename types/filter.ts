export interface FilterOption {
  label: string;
  value: string;
}

export interface BirthdayFilter {
  type: 'month' | 'range';
  month?: number;
  startDate?: Date;
  endDate?: Date;
}

export interface FilterOptions {
  location?: string;
  community?: string;
  diocese?: string;
  region?: string;
  type?: string;
  types?: string[];
  birthdayFilter?: BirthdayFilter;
  // Add other filter options as needed
}

export interface FilterState {
  category: string | null;
  subcategory: string | null;
  options: FilterOptions;
} 