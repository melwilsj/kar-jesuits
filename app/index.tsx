import { Redirect } from 'expo-router';
import { useAuth } from '../hooks/useAuth';

export default function Index() {
  const { isAuthenticated } = useAuth();
  
  // Redirect to the appropriate stack based on auth state
  return <Redirect href={isAuthenticated ? "/(app)/home" : "/(auth)/login"} />;
}
