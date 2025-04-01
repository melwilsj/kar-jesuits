import { Drawer } from 'expo-router/drawer';
import { useColorScheme } from '@/hooks/useSettings';
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
            backgroundColor: Colors[`${colorScheme}`].background,
          },
          headerTintColor: isDark ? '#fff' : '#000',
          drawerStyle: {
            backgroundColor: Colors[`${colorScheme}`].background,
          },
          drawerActiveTintColor: Colors[`${colorScheme}`].primary,
          drawerInactiveTintColor: Colors[`${colorScheme}`].gray400,
          headerTitle: '',
          headerLeft: () => <SearchHeader />,
        }}
        drawerContent={(props) => <CustomDrawer {...props} />}
      >
        <Drawer.Screen name="home" 
          options={{ drawerLabel: 'Home' }} />
        <Drawer.Screen name="filter/index" 
          options={{ drawerLabel: 'Filter' }} />
        <Drawer.Screen name="space/index" 
          options={{ drawerLabel: 'Space' }} />
        <Drawer.Screen name="settings" 
          options={{ drawerLabel: 'Settings' }} />
      </Drawer>
      <TabBar />
    </>
  );
}