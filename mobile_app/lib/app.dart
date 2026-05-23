import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/theme/app_theme.dart';
import 'providers/app_state.dart';
import 'screens/home_screen.dart';
import 'screens/login_screen.dart';

class MedRepFleetApp extends StatelessWidget {
  const MedRepFleetApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MedRep Fleet',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      home: Consumer<AppState>(
        builder: (context, state, _) {
          if (!state.initialized) {
            return const Scaffold(
              body: Center(child: CircularProgressIndicator()),
            );
          }
          return state.user != null ? const HomeScreen() : const LoginScreen();
        },
      ),
    );
  }
}
