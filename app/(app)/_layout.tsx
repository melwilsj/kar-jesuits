import { Drawer } from 'expo-router/drawer';
import { useColorScheme } from 'react-native';
import Colors from '../../constants/Colors';
import CustomDrawer from '@/components/CustomDrawer';
import SearchHeader from '@/components/SearchHeader';
import TabBar from '@/components/TabBar';

export default function AppLayout() {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  
  return (
    <>
      <Drawer
        screenOptions={{
          headerStyle: {
            backgroundColor: isDark ? '#000' : '#fff',
          },
          headerTintColor: isDark ? '#fff' : '#000',
          drawerStyle: {
            backgroundColor: isDark ? '#000' : '#fff',
          },
          drawerActiveTintColor: Colors.primary,
          drawerInactiveTintColor: Colors.gray[400],
          headerTitle: '',
          headerLeft: () => <SearchHeader />,
        }}
        drawerContent={(props) => <CustomDrawer {...props} />}
      >
        <Drawer.Screen name="home" 
          options={{ drawerLabel: 'Home' }} />
        <Drawer.Screen name="filter" 
          options={{ drawerLabel: 'Filter' }} />
        <Drawer.Screen name="documents" 
          options={{ drawerLabel: 'Documents' }} />
        <Drawer.Screen name="settings" 
          options={{ drawerLabel: 'Settings' }} />
        <Drawer.Screen name="profile/[id]" 
          options={{ drawerItemStyle: { display: 'none' } }} />
        <Drawer.Screen name="community/[id]" 
          options={{ drawerItemStyle: { display: 'none' } }} />
      </Drawer>
      <TabBar />
    </>
  );
}