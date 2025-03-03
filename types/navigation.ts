import { ParamListBase } from '@react-navigation/native';

export interface AuthStackParamList extends ParamListBase {
  login: undefined;
  verify: {
    verificationId: string;
    phoneNumber: string;
  };
}

export interface AppTabParamList extends ParamListBase {
  home: undefined;
  profile: undefined;
} 